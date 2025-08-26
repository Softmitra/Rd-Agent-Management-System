<?php

namespace App\Http\Controllers;

use App\Exports\CollectionsExport;
use App\Models\Collection;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\RDAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $agent = Auth::user();
        $query = Collection::where('agent_id', $agent->id)
            ->with(['customer'])
            ->latest();

        if ($request->filled('customer_name')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer_name . '%');
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $collections = $query->paginate(10)->withQueryString();
        return view('agent.collections.index', compact('collections'));
    }

    public function create(Request $request)
    {
        $agent = Auth::user();
        $rdAccounts = collect();
        $customers = collect();
        
        // Handle AJAX requests for customer search
        if ($request->ajax() || $request->hasAny(['search_name', 'search_mobile', 'search_cif'])) {
            
            // Debug: Show all customers for this agent
            if ($request->filled('debug_all_customers')) {
                $allCustomers = Customer::where('agent_id', Auth::id())->get();
                
                Log::info('Debug: All customers for agent', [
                    'agent_id' => Auth::id(),
                    'total_customers' => $allCustomers->count(),
                    'customers' => $allCustomers->map(function($customer) {
                        return [
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'mobile' => $customer->mobile_number,
                            'cif_id' => $customer->cif_id
                        ];
                    })
                ]);
                
                if ($request->ajax()) {
                    return response()->json([
                        'customers' => $allCustomers,
                        'success' => true,
                        'message' => "Found {$allCustomers->count()} customers for agent " . Auth::id(),
                        'debug' => true
                    ]);
                }
            }
            
            // Customer search logic
            if ($request->hasAny(['search_name', 'search_mobile', 'search_cif'])) {
                $query = Customer::where('agent_id', Auth::id());
                
                $searchName = $request->input('search_name');
                $searchMobile = $request->input('search_mobile');
                $searchCif = $request->input('search_cif');
                
                // Log search parameters for debugging
                Log::info('Customer search parameters', [
                    'agent_id' => Auth::id(),
                    'search_name' => $searchName,
                    'search_mobile' => $searchMobile,
                    'search_cif' => $searchCif,
                    'is_ajax' => $request->ajax()
                ]);
                
                $query->where(function ($q) use ($searchName, $searchMobile, $searchCif) {
                    $hasCondition = false;
                    
                    if (!empty($searchName)) {
                        $q->where('name', 'like', "%$searchName%");
                        $hasCondition = true;
                    }
                    
                    if (!empty($searchMobile)) {
                        if ($hasCondition) {
                            $q->orWhere('mobile_number', 'like', "%$searchMobile%");
                        } else {
                            $q->where('mobile_number', 'like', "%$searchMobile%");
                            $hasCondition = true;
                        }
                    }
                    
                    if (!empty($searchCif)) {
                        if ($hasCondition) {
                            $q->orWhere('cif_id', 'like', "%$searchCif%");
                        } else {
                            $q->where('cif_id', 'like', "%$searchCif%");
                        }
                    }
                });
                
                $customers = $query->get();
                
                // Log search results
                Log::info('Customer search results', [
                    'agent_id' => Auth::id(),
                    'found_customers' => $customers->count(),
                    'customers' => $customers->map(function($customer) {
                        return [
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'mobile' => $customer->mobile_number,
                            'cif_id' => $customer->cif_id
                        ];
                    })
                ]);
                
                if ($request->ajax()) {
                    return response()->json([
                        'customers' => $customers,
                        'success' => true,
                        'message' => $customers->count() > 0 ? 'Customers found' : 'No customers found'
                    ]);
                }
            }
            
            // Account search by customer ID
            if ($request->filled('customer_id')) {
                $customerId = $request->input('customer_id');
                
                // Debug: Log the customer ID and agent ID
                Log::info('DEBUG: Searching RD accounts', [
                    'customer_id' => $customerId,
                    'agent_id' => $agent->id,
                    'agent_email' => $agent->email,
                ]);
                
                // First check if customer exists and belongs to this agent
                $customerCheck = Customer::where('id', $customerId)
                    ->where('agent_id', $agent->id)
                    ->first();
                    
                Log::info('DEBUG: Customer check', [
                    'customer_found' => $customerCheck ? true : false,
                    'customer_name' => $customerCheck ? $customerCheck->name : 'Not found',
                    'customer_agent_id' => $customerCheck ? $customerCheck->agent_id : null,
                ]);
                
                // Get all RD accounts for this customer and agent
                $rdAccountsQuery = RDAccount::where('agent_id', $agent->id)
                    ->where('customer_id', $customerId)
                    ->with('customer');
                    
                // Debug: Log the SQL query
                Log::info('DEBUG: RD Accounts Query', [
                    'sql' => $rdAccountsQuery->toSql(),
                    'bindings' => $rdAccountsQuery->getBindings(),
                ]);
                
                $rdAccounts = $rdAccountsQuery->get();
                    
                Log::info('DEBUG: RD Accounts found', [
                    'count' => $rdAccounts->count(),
                    'accounts' => $rdAccounts->map(function($acc) {
                        return [
                            'id' => $acc->id ?? null,
                            'account_number' => $acc->account_number ?? null,
                            'customer_id' => $acc->customer_id ?? null,
                            'agent_id' => $acc->agent_id ?? null,
                            'status' => $acc->status ?? null,
                            'customer_name' => $acc->customer ? $acc->customer->name : 'N/A',
                        ];
                    })
                ]);
                
                // Continue with mapping
                $rdAccounts = $rdAccounts
                    ->map(function($account) use ($agent) {
                        // Now we have model instances, so append() will work
                        $account->append([
                            'missed_months',
                            'penalty_per_month',
                            'total_penalty',
                            'total_payable',
                            'is_discontinued',
                            'computed_status'
                        ]);
                        
                        // Calculate collection information using collections table
                        $monthlyAmount = $account->monthly_amount;
                        $totalDeposited = $account->total_deposited ?? 0; // From RD account table
                        
                        // Get collections for this customer
                        $customerCollections = Collection::where('customer_id', $account->customer_id)
                            ->where('agent_id', $agent->id)
                            ->latest()
                            ->limit(10)
                            ->get();
                        
                        $totalPaidThroughCollections = $customerCollections->sum('amount'); // From collections table
                        $installmentsPaid = $account->installments_paid ?? 0; // Paid installments from RD table
                        
                        // Calculate time-based information
                        $startDate = \Carbon\Carbon::parse($account->start_date);
                        $currentDate = \Carbon\Carbon::now();
                        $monthsElapsed = $startDate->diffInMonths($currentDate); // Total months since opening
                        
                        // Debug logging
                        Log::info('Months calculation debug', [
                            'account_number' => $account->account_number,
                            'start_date' => $account->start_date,
                            'current_date' => $currentDate->format('Y-m-d'),
                            'months_elapsed' => $monthsElapsed,
                            'installments_paid' => $installmentsPaid,
                            'monthly_amount' => $monthlyAmount
                        ]);
                        
                        // Expected installments should be paid by now (considering monthly frequency)
                        $expectedInstallments = $monthsElapsed;
                        
                        // Total pending installments = Expected - Actually paid
                        $totalPendingInstallments = max(0, $expectedInstallments - $installmentsPaid);
                        
                        // Get last collection first
                        $lastCollection = $customerCollections->first();
                        $lastCollectionDate = $lastCollection ? $lastCollection->date : null;
                        
                        // For collection purpose - show actual pending installments
                        // Not months since last payment, but actual deficit
                        $currentPendingMonths = $totalPendingInstallments;
                        
                        // Limit to reasonable collection amount (max 3 months at once)
                        $collectionLimitMonths = min($currentPendingMonths, 3);
                        
                        // Pending amount for collection = actual pending months
                        $pendingAmountForCollection = $collectionLimitMonths * $monthlyAmount;
                        
                        // Debug final calculation
                        Log::info('Pending calculation debug', [
                            'account_number' => $account->account_number,
                            'expected_installments' => $expectedInstallments,
                            'installments_paid' => $installmentsPaid,
                            'total_pending_installments' => $totalPendingInstallments,
                            'collection_limit_months' => $collectionLimitMonths,
                            'pending_amount_for_collection' => $pendingAmountForCollection
                        ]);
                        
                        // Total deficit (for reference)
                        $totalDeficit = $totalPendingInstallments * $monthlyAmount;
                        
                        // Expected total amount if all installments were paid
                        $expectedTotalAmount = $expectedInstallments * $monthlyAmount;
                        
                        // Calculate next due date
                        $nextDueDate = $lastCollectionDate ? 
                            \Carbon\Carbon::parse($lastCollectionDate)->addMonth() : 
                            $startDate->copy()->addMonth();
                        
                        // Check if payment is overdue
                        $isOverdue = $nextDueDate->isPast();
                        $overdueMonths = $isOverdue ? $nextDueDate->diffInMonths($currentDate) : 0;
                        
                        // Determine priority based on pending installments
                        $priority = 'normal';
                        if ($totalPendingInstallments >= 3) {
                            $priority = 'high';
                        } elseif ($totalPendingInstallments >= 1) {
                            $priority = 'medium';
                        }
                        
                        // Count actual collections made
                        $collectionsCount = $customerCollections->count();
                        
                        // Payment completion ratio
                        $paymentRatio = $expectedInstallments > 0 ? (($installmentsPaid / $expectedInstallments) * 100) : 0;
                        
                        // Return array with penalty calculations
                        return [
                            'id' => $account->id,
                            'account_number' => $account->account_number,
                            'customer_name' => $account->customer ? $account->customer->name : 'N/A',
                            'monthly_amount' => $monthlyAmount,
                            'missed_months' => $account->missed_months, // New: Number of defaulted months
                            'penalty_per_month' => $account->penalty_per_month, // New: Penalty per month
                            'total_penalty' => $account->total_penalty, // New: Total penalty amount
                            'total_payable' => $account->total_payable, // New: Total amount to pay including penalty
                            'computed_status' => $account->computed_status, // New: Status considering discontinuation
                            'is_discontinued' => $account->is_discontinued, // New: Discontinued flag
                            
                            // Existing fields for backward compatibility
                            'total_deposited' => $totalDeposited,
                            'total_paid_collections' => $totalPaidThroughCollections,
                            'installments_paid' => $installmentsPaid,
                            'months_elapsed' => $monthsElapsed,
                            'expected_installments' => $expectedInstallments,
                            'pending_installments' => $totalPendingInstallments,
                            'current_pending_months' => $collectionLimitMonths,
                            'pending_amount' => $pendingAmountForCollection,
                            'total_deficit' => $totalDeficit,
                            'expected_total_amount' => $expectedTotalAmount,
                            'payment_ratio' => round($paymentRatio, 1),
                            'start_date' => $account->start_date,
                            'status' => $account->status,
                            'last_collection_date' => $lastCollectionDate,
                            'next_due_date' => $nextDueDate->format('Y-m-d'),
                            'is_overdue' => $isOverdue,
                            'overdue_months' => $overdueMonths,
                            'priority' => $priority,
                            'half_month_period' => $account->half_month_period,
                            'duration_months' => $account->duration_months,
                            'maturity_date' => $account->maturity_date,
                            'collections_count' => $collectionsCount,
                            'recent_collections' => $customerCollections->take(3)->map(function($collection) {
                                return [
                                    'date' => $collection->date,
                                    'amount' => $collection->amount,
                                    'payment_type' => $collection->payment_type,
                                    'status' => $collection->status
                                ];
                            })
                        ];
                    });
                
                Log::info('RD Accounts search results with collection info', [
                    'agent_id' => $agent->id,
                    'customer_id' => $customerId,
                    'found_accounts' => $rdAccounts->count(),
                    'accounts_summary' => $rdAccounts->map(function($account) {
                        return [
                            'account_number' => $account['account_number'],
                            'total_deposited' => $account['total_deposited'],
                            'total_paid_collections' => $account['total_paid_collections'],
                            'installments_paid' => $account['installments_paid'],
                            'pending_amount' => $account['pending_amount'],
                            'pending_installments' => $account['pending_installments'],
                            'priority' => $account['priority'],
                            'payment_ratio' => $account['payment_ratio']
                        ];
                    })
                ]);
                
                if ($request->ajax()) {
                    // Ensure we're returning a plain array for JavaScript
                    $rdAccountsArray = $rdAccounts->values()->toArray();
                    
                    Log::info('DEBUG: Final response data', [
                        'rd_accounts_count' => count($rdAccountsArray),
                        'is_array' => is_array($rdAccountsArray),
                        'first_account' => count($rdAccountsArray) > 0 ? $rdAccountsArray[0] : null
                    ]);
                    
                    return response()->json([
                        'rd_accounts' => $rdAccountsArray,
                        'success' => true,
                        'message' => count($rdAccountsArray) > 0 ? 'Accounts found with collection details' : 'No accounts found'
                    ]);
                }
            }
        }
        
        // Handle old search functionality for backward compatibility
        if ($request->filled('search')) {
            $search = $request->input('search');
            
            Log::info('Collection search started', [
                'agent_id' => $agent->id,
                'search_term' => $search
            ]);
            
            if ($search === 'debug_all') {
                $rdAccounts = RDAccount::where('agent_id', $agent->id)->with('customer')->get();
                Log::info('Debug: Returning ALL RD accounts for agent', ['count' => $rdAccounts->count()]);
            } else {
                $query = RDAccount::where('agent_id', $agent->id)
                    ->with('customer');

                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('mobile_number', 'like', "%$search%")
                        ->orWhere('cif_id', 'like', "%$search%");
                });

                $rdAccounts = $query->get();
            }
            
            Log::info('Collection search results', [
                'search_term' => $search,
                'found_accounts' => $rdAccounts->count(),
                'accounts' => $rdAccounts->map(function($account) {
                    return [
                        'id' => $account->id,
                        'account_number' => $account->account_number,
                        'customer_name' => $account->customer->name ?? 'No customer',
                        'customer_mobile' => $account->customer->mobile_number ?? 'No mobile',
                        'status' => $account->status,
                        'agent_id' => $account->agent_id
                    ];
                })
            ]);
        }

        // Get all customers for the logged-in agent for initial load
        if ($customers->isEmpty()) {
            $customers = Customer::where('agent_id', Auth::id())->get();
        }

        return view('agent.collections.create', compact('rdAccounts', 'customers'));
    }



    public function store(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date',
                'payment_type' => 'required|in:cash,upi,cheque,online',
                'amount' => 'required|numeric|min:0',
                'note' => 'nullable|string',
                'customer_id' => 'required|exists:customers,id',
                'receipt_number' => 'nullable|string|max:255',
            ]);

            // Verify the customer belongs to the agent and has active RD accounts
            $customer = Customer::where('id', $request->customer_id)
                ->where('agent_id', auth()->id())
                ->first();

            if (!$customer) {
                return redirect()
                    ->back()
                    ->with('error', 'Customer not found or not assigned to you')
                    ->withInput();
            }

            // Check if customer has any active RD accounts with this agent
            $hasActiveAccounts = RDAccount::where('customer_id', $request->customer_id)
                ->where('agent_id', auth()->id())
                ->where('status', 'active')
                ->exists();

            if (!$hasActiveAccounts) {
                return redirect()
                    ->back()
                    ->with('error', 'No active RD accounts found for this customer')
                    ->withInput();
            }

            // Create the collection
            Collection::create([
                'date' => $request->date,
                'payment_type' => $request->payment_type,
                'amount' => $request->amount,
                'note' => $request->note,
                'agent_id' => auth()->id(),
                'customer_id' => $request->customer_id,
                'receipt_number' => $request->receipt_number,
                'status' => 'submitted'
            ]);

            return redirect()
                ->route('collections.index')
                ->with('success', 'Collection recorded successfully!');
        } catch (\Exception $e) {
            Log::error('Collection creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Failed to record collection: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $account = RDAccount::with(['customer' => function ($query) {
                $query->withDefault([
                    'name' => 'N/A',
                    'mobile_number' => 'N/A',
                    'cif_id' => 'N/A'
                ]);
            }])->findOrFail($id);

            $payments = Payment::where('rd_account_id', $account->id)
                ->latest()
                ->limit(5)
                ->get();

            return view('agent.collections.show', [
                'account' => $account,
                'payments' => $payments,
                'customer' => $account->customer
            ]);
        } catch (\Exception $e) {
            return redirect()
                ->route('collections.create')
                ->with('error', 'Invalid RD Account. Please try again.');
        }
    }

    public function export(Request $request)
    {
        $userId = auth()->id();
        $logData = [
            'user_id' => $userId,
            'search' => $request->search,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'timestamp' => now()->toDateTimeString(),
        ];

        Log::info('Collection export started.', $logData);

        $query = Collection::with(['customer', 'rdAccount'])
            ->where('agent_id', $userId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q
                    ->whereHas('customer', function ($q) use ($search) {
                        $q
                            ->where('name', 'like', "%$search%")
                            ->orWhere('mobile_number', 'like', "%$search%");
                    })
                    ->orWhereHas('rdAccount', function ($q) use ($search) {
                        $q->where('account_number', 'like', "%$search%");
                    });
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $collections = $query->latest()->get();

        Log::info('Collection export completed.', [
            'user_id' => $userId,
            'records_exported' => $collections->count()
        ]);
        $fileName = 'collection_' . date('Y-m-d_H-i-s') . '.xlsx';

        // return Excel::download(new CollectionsExport($collections),
        //     'collections_' . date('Y-m-d_H-i-s') . '.xlsx');

        return Excel::download(new CollectionsExport($collections), $fileName);
    }
    
    public function exportList(Request $request)
    {
        $userId = auth()->id();
        $logData = [
            'user_id' => $userId,
            'search' => $request->search,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'timestamp' => now()->toDateTimeString(),
        ];

        Log::info('Collection export started.', $logData);

        $query = Collection::with(['customer', 'rdAccount'])
            ->where('agent_id', $userId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q
                    ->whereHas('customer', function ($q) use ($search) {
                        $q
                            ->where('name', 'like', "%$search%")
                            ->orWhere('mobile_number', 'like', "%$search%");
                    })
                    ->orWhereHas('rdAccount', function ($q) use ($search) {
                        $q->where('account_number', 'like', "%$search%");
                    });
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $collections = $query->latest()->get();

        Log::info('Collection export completed.', [
            'user_id' => $userId,
            'records_exported' => $collections->count()
        ]);
        $fileName = 'collection_' . date('Y-m-d_H-i-s') . '.xlsx';

        // return Excel::download(new CollectionsExport($collections),
        //     'collections_' . date('Y-m-d_H-i-s') . '.xlsx');

        return Excel::download(new CollectionsExport($collections), $fileName);
    }
}
