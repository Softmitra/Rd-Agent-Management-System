<?php

namespace App\Http\Controllers;

use App\Exports\RDAccountsExport;
use App\Models\Customer;
use App\Models\RDAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class RDAgentAccount extends Controller
{
    /**
     * Display a listing of RD accounts.
     */
    public function index(Request $request)
    {
        // Only show RD accounts for the logged-in agent
        $query = RDAccount::withoutGlobalScopes()
                         ->with(['customer', 'agent'])
                         ->where('agent_id', Auth::id());
        
        // Get completion counts
        $totalRdAccounts = RDAccount::where('agent_id', Auth::id())->count();
        $incompleteRdAccounts = RDAccount::where('agent_id', Auth::id())->incomplete()->count();
        $completeRdAccounts = $totalRdAccounts - $incompleteRdAccounts;

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q
                    ->where('account_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Handle export request
        if ($request->has('export')) {
            return $this->export($request);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('min_amount')) {
            $query->where('monthly_amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('monthly_amount', '<=', $request->max_amount);
        }

        // Clone query for summary
        $summaryQuery = clone $query;
        $summary = [
            'total_accounts' => $summaryQuery->count(),
            'total_monthly_deposits' => $summaryQuery->sum('monthly_amount'),
            'status_counts' => [
                'active' => $summaryQuery->clone()->where('status', 'active')->count(),
                'matured' => $summaryQuery->clone()->where('status', 'matured')->count(),
                'closed' => $summaryQuery->clone()->where('status', 'closed')->count(),
            ]
        ];

        // Get paginated results
        $rdAccounts = $query->latest()->paginate(10)->withQueryString();

        return view('agent.rd-agent-accounts.index', compact('rdAccounts', 'summary', 'totalRdAccounts', 'incompleteRdAccounts', 'completeRdAccounts'));
    }

    /**
     * Show the form for creating a new RD account.
     */
    public function create()
    {
        // Get only customers assigned to the logged-in agent
        $customers = Customer::where('agent_id', Auth::id())->get();
        return view('agent.rd-agent-accounts.create', compact('customers'));
    }

    /**
     * Store a newly created RD account in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'monthly_amount' => 'required|numeric|min:100|regex:/^[1-9]\d*00$/',
                'opening_date' => 'required|date_format:d/m/Y',
                'total_deposited' => 'required|numeric|min:0',
                'installments_paid' => 'required|integer|min:0',
                'account_number' => 'required|string|unique:rd_accounts,account_number',
                'registered_phone' => 'nullable|string|size:10',
                'is_joint_account' => 'boolean',
                'joint_holder_name' => 'required_if:is_joint_account,1',
                'note' => 'nullable|string|max:500'
            ], [
                'monthly_amount.regex' => 'Monthly amount must be in multiples of ₹100.',
                'monthly_amount.min' => 'Monthly amount must be at least ₹100.',
                'installments_paid.min' => 'Installments paid cannot be negative.',
            ]);

            // Set default duration months to 60
            $validated['duration_months'] = 60;

            // Convert date from d/m/Y to Y-m-d format
            $openingDate = Carbon::createFromFormat('d/m/Y', $validated['opening_date']);

            // Remove opening_date as it's not in the database
            unset($validated['opening_date']);

            $validated['start_date'] = $openingDate->format('Y-m-d');

            // Calculate installments paid based on total deposited amount
            $validated['installments_paid'] = (int) floor($validated['total_deposited'] / $validated['monthly_amount']);

            // Ensure the customer belongs to the logged-in agent
            $customer = Customer::where('id', $validated['customer_id'])
                               ->where('agent_id', Auth::id())
                               ->firstOrFail();
            
            // Auto-assign registered phone from customer if not provided
            if (empty($validated['registered_phone'])) {
                $validated['registered_phone'] = $customer->mobile_number;
            }
            
            // Auto-assign the logged-in agent
            $validated['agent_id'] = Auth::id();

            $validated['account_type'] = 'RD';  // Set default account type
            $validated['status'] = 'active';
            $validated['maturity_date'] = $openingDate->copy()->addMonths($validated['duration_months'])->format('Y-m-d');
            $validated['maturity_amount'] = $validated['monthly_amount'] * $validated['duration_months'];
            $validated['interest_rate'] = 6.5;  // Default interest rate
            $validated['created_by'] = Auth::id();
            $validated['updated_by'] = Auth::id();
            $validated['half_month_period'] = $openingDate->day <= 15 ? 'first' : 'second';

            // Set default value for is_joint_account if not provided
            if (!isset($validated['is_joint_account'])) {
                $validated['is_joint_account'] = false;
            }

            // For debugging
            Log::info('Creating RD Account with data:', $validated);

            $account = RDAccount::create($validated);

            Log::info('RD Account created successfully:', ['id' => $account->id, 'installments_paid' => $account->installments_paid]);

            return redirect()
                ->route('agent.rd-agent-accounts.index')
                ->with('success', 'RD Account created successfully.');
        } catch (\Exception $e) {
            Log::error('RD Account creation failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Failed to create RD Account. Please check all fields and try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified RD account.
     */
    public function show(RDAccount $rdAccount)
    {
        // Debug logging
        Log::info('RD Account Show Debug', [
            'rdAccount_id' => $rdAccount->id,
            'rdAccount_agent_id' => $rdAccount->agent_id,
            'auth_user_id' => Auth::id(),
            'auth_user' => Auth::user()
        ]);
        
        // Ensure the RD account belongs to the logged-in agent
        if ($rdAccount->agent_id !== Auth::id()) {
            Log::warning('Unauthorized RD Account access attempt', [
                'rdAccount_id' => $rdAccount->id,
                'rdAccount_agent_id' => $rdAccount->agent_id,
                'auth_user_id' => Auth::id()
            ]);
            abort(403, 'Unauthorized access to this RD account. This account belongs to agent ID: ' . $rdAccount->agent_id . ', but you are agent ID: ' . Auth::id());
        }
        
        $rdAccount->load(['customer', 'agent', 'creator', 'updater']);
        return view('agent.rd-agent-accounts.show', compact('rdAccount'));
    }

    /**
     * Show the form for editing the specified RD account.
     */
    public function edit(RDAccount $rdAccount)
    {
        // Debug logging
        Log::info('RD Account Edit Debug', [
            'rdAccount_id' => $rdAccount->id,
            'rdAccount_agent_id' => $rdAccount->agent_id,
            'auth_user_id' => Auth::id(),
            'auth_user' => Auth::user()
        ]);
        
        // Ensure the RD account belongs to the logged-in agent
        if ($rdAccount->agent_id !== Auth::id()) {
            Log::warning('Unauthorized RD Account edit attempt', [
                'rdAccount_id' => $rdAccount->id,
                'rdAccount_agent_id' => $rdAccount->agent_id,
                'auth_user_id' => Auth::id()
            ]);
            abort(403, 'Unauthorized access to this RD account. This account belongs to agent ID: ' . $rdAccount->agent_id . ', but you are agent ID: ' . Auth::id());
        }
        
        // Get only customers assigned to the logged-in agent
        $customers = Customer::where('agent_id', Auth::id())->get();
        return view('agent.rd-agent-accounts.edit', compact('rdAccount', 'customers'));
    }

    /**
     * Update the specified RD account in storage.
     */
    public function update(Request $request, RDAccount $rdAccount)
    {
        try {
            // Ensure the RD account belongs to the logged-in agent
            if ($rdAccount->agent_id !== Auth::id()) {
                abort(403, 'Unauthorized access to this RD account.');
            }
            
            $validated = $request->validate([
                'account_number' => 'required|string|unique:rd_accounts,account_number,' . $rdAccount->id,
                'customer_id' => 'required|exists:customers,id',
                'monthly_amount' => 'required|numeric|min:100|regex:/^[1-9]\d*00$/',
                'opening_date' => 'required|date_format:d/m/Y',
                'duration_months' => 'required|integer|min:1|max:120',
                'interest_rate' => 'required|numeric|min:0|max:100',
                'status' => 'required|in:active,matured,closed',
                'registered_phone' => 'nullable|string|size:10|regex:/^[0-9]{10}$/',
                'note' => 'nullable|string|max:500',
                'is_joint_account' => 'boolean',
                'joint_holder_name' => 'required_if:is_joint_account,1|nullable|string|max:255',
            ], [
                'monthly_amount.regex' => 'Monthly amount must be in multiples of ₹100.',
                'registered_phone.regex' => 'Phone number must be exactly 10 digits.',
                'duration_months.max' => 'Duration cannot exceed 120 months (10 years).',
            ]);

            // Ensure the customer belongs to the logged-in agent
            $customer = Customer::where('id', $validated['customer_id'])
                               ->where('agent_id', Auth::id())
                               ->firstOrFail();

            // Convert date format from d/m/Y to Y-m-d
            $validated['start_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['opening_date'])->format('Y-m-d');
            unset($validated['opening_date']);

            // Handle joint account
            $validated['is_joint_account'] = $request->has('is_joint_account');
            if (!$validated['is_joint_account']) {
                $validated['joint_holder_name'] = null;
            }

            // Calculate maturity date
            $validated['maturity_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->opening_date)
                ->addMonths($validated['duration_months'])
                ->format('Y-m-d');

            // Calculate maturity amount
            $totalDeposits = $validated['monthly_amount'] * $validated['duration_months'];
            $interest = $totalDeposits * ($validated['interest_rate'] / 100);
            $validated['maturity_amount'] = $totalDeposits + $interest;

            // Set updated_by
            $validated['updated_by'] = Auth::id();

            // Log the update
            Log::info('Updating RD Account', [
                'account_id' => $rdAccount->id,
                'agent_id' => Auth::id(),
                'changes' => $validated
            ]);

            $rdAccount->update($validated);

            return redirect()
                ->route('agent.rd-agent-accounts.show', $rdAccount)
                ->with('success', 'RD Account updated successfully.');

        } catch (\Exception $e) {
            Log::error('RD Account update failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Failed to update RD Account. Please check all fields and try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified RD account from storage.
     */
    public function destroy(RDAccount $rdAccount)
    {
        // Ensure the RD account belongs to the logged-in agent
        if ($rdAccount->agent_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this RD account.');
        }
        
        $rdAccount->delete();
        return redirect()
            ->route('agent.rd-agent-accounts.index')
            ->with('success', 'RD Account deleted successfully.');
    }

    /**
     * Close an RD account.
     */
    public function close(RDAccount $rdAccount)
    {
        // Ensure the RD account belongs to the logged-in agent
        if ($rdAccount->agent_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this RD account.');
        }
        
        if ($rdAccount->status === 'closed') {
            return back()->with('error', 'RD Account is already closed.');
        }

        $rdAccount->update([
            'status' => 'closed',
            'closed_at' => Carbon::now(),
        ]);

        return redirect()
            ->route('agent.rd-agent-accounts.show', $rdAccount)
            ->with('success', 'RD Account closed successfully.');
    }

    /**
     * Mark an RD account as matured.
     */
    public function mature(RDAccount $rdAccount)
    {
        // Ensure the RD account belongs to the logged-in agent
        if ($rdAccount->agent_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this RD account.');
        }
        
        if ($rdAccount->status === 'matured') {
            return back()->with('error', 'RD Account is already marked as matured.');
        }

        $rdAccount->update([
            'status' => 'matured',
            'matured_at' => Carbon::now(),
        ]);

        return redirect()
            ->route('agent.rd-agent-accounts.show', $rdAccount)
            ->with('success', 'RD Account marked as matured successfully.');
    }

    /**
     * Export RD accounts based on filters and format
     */
    public function export(Request $request)
    {
        // Only export RD accounts for the logged-in agent
        $query = RDAccount::with(['customer', 'agent'])
                         ->where('agent_id', Auth::id());

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('min_amount')) {
            $query->where('monthly_amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('monthly_amount', '<=', $request->max_amount);
        }

        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('maturity_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q
                    ->where('account_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $rdAccounts = $query->latest()->get();
        $agentName = Auth::user()->name;
        $fileName = 'rd_accounts_' . str_replace(' ', '_', $agentName) . '_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new RDAccountsExport($rdAccounts), $fileName);
    }

    /**
     * Get incomplete RD accounts for completion modal
     */
    public function getIncompleteRdAccounts()
    {
        // Get current authenticated agent
        $agent = Auth::user();
        
        $incompleteRdAccounts = RDAccount::where('agent_id', $agent->id)
            ->incomplete()
            ->with(['customer'])
            ->limit(10) // Show only first 10 for completion
            ->get();
            
        return response()->json([
            'rdAccounts' => $incompleteRdAccounts,
            'count' => $incompleteRdAccounts->count()
        ]);
    }
    
    /**
     * Complete RD account details
     */
    public function completeRdAccount(Request $request, RDAccount $rdAccount)
    {
        // Get current authenticated agent
        $agent = Auth::user();
        
        // Ensure RD account belongs to this agent
        if ($rdAccount->agent_id !== $agent->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'aslaas_number' => 'required|string|max:255',
            'registered_phone' => 'required|string|size:10',
            'monthly_amount' => 'required|numeric|min:100',
            'start_date' => 'required|date',
            'note' => 'nullable|string|max:500',
        ]);
        
        // Apply same logic as RD account creation
        // Set default duration months to 60 (same as creation logic)
        $validated['duration_months'] = 60;
        
        // Set default interest rate to 6.5% (same as creation logic)
        $validated['interest_rate'] = 6.5;
        
        // Auto-calculate maturity date based on start_date + duration_months
        $startDate = Carbon::parse($validated['start_date']);
        $validated['maturity_date'] = $startDate->copy()->addMonths($validated['duration_months'])->format('Y-m-d');
        
        // Auto-calculate maturity amount
        $validated['maturity_amount'] = $validated['monthly_amount'] * $validated['duration_months'];
        
        // Auto-calculate half_month_period based on start_date day
        $validated['half_month_period'] = $startDate->day <= 15 ? 'first' : 'second';
        
        // Set other default values if not already set
        if (empty($rdAccount->account_type)) {
            $validated['account_type'] = 'RD';
        }
        if (empty($rdAccount->status)) {
            $validated['status'] = 'active';
        }
        
        // Set updated_by
        $validated['updated_by'] = $agent->id;
        
        $rdAccount->update($validated);
        
        // Mark RD account as complete if information is sufficient
        if ($rdAccount->isInfoComplete()) {
            $rdAccount->markAsComplete('Completed via completion modal');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'RD Account details completed successfully.',
            'rdAccount' => $rdAccount
        ]);
    }
}
