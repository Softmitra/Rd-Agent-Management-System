<?php

namespace App\Http\Controllers;

use App\Models\RDAccount;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $agent = Auth::user();
        $query = Payment::where('agent_id', $agent->id)
            ->with(['rdAccount', 'rdAccount.customer'])
            ->latest();

        // Apply filters
        if ($request->filled('account_number')) {
            $query->whereHas('rdAccount', function($q) use ($request) {
                $q->where('account_number', 'like', '%' . $request->account_number . '%');
            });
        }

        if ($request->filled('customer_name')) {
            $query->whereHas('rdAccount.customer', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer_name . '%');
            });
        }

        if ($request->filled('payment_date')) {
            $query->whereDate('payment_date', $request->payment_date);
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
        
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query = RDAccount::where('agent_id', $agent->id)
                ->where('status', 'active')
                ->with('customer');

            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('mobile_number', 'like', "%$search%")
                  ->orWhere('cif_id', 'like', "%$search%");
            });

            $rdAccounts = $query->get();
        }
        
        return view('agent.collections.create', compact('rdAccounts'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'rd_account_id' => 'required|exists:rd_accounts,id',
                'pending_months' => 'required|integer|min:1|max:12',
                'amount' => 'required|numeric|min:0',
                'payment_date' => 'required|date',
                'payment_mode' => 'required|in:cash,cheque,online,upi',
                'cheque_number' => 'required_if:payment_mode,cheque|nullable',
                'bank_name' => 'required_if:payment_mode,cheque|nullable',
                'transaction_id' => 'required_if:payment_mode,online|nullable',
                'upi_id' => 'required_if:payment_mode,upi|nullable',
            ]);

            $rdAccount = RDAccount::findOrFail($request->rd_account_id);
            $monthsPaid = $request->pending_months;

            // Generate receipt number
            $receiptNumber = 'COLL' . date('Ymd') . str_pad(Payment::count() + 1, 6, '0', STR_PAD_LEFT);
 
            // Create payment record
            $payment = Payment::create([
                'rd_account_id' => $rdAccount->id,
                'customer_id' => $rdAccount->customer_id,
                'agent_id' => auth()->id(),
                'receipt_number' => $receiptNumber,
                'amount' => $request->amount,
                'payment_date' => Carbon::parse($request->payment_date),
                'payment_method' => $request->payment_mode,
                'cheque_number' => $request->cheque_number,
                'bank_name' => $request->bank_name,
                'transaction_id' => $request->transaction_id,
                'upi_id' => $request->upi_id,
                'status' => 'Not in Lot',
                'remarks' => "Collection for {$monthsPaid} month(s)",
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            // Update RD account
            $rdAccount->update([
                'total_deposited' => $rdAccount->total_deposited + $request->amount,
                'installments_paid' => $rdAccount->installments_paid + $monthsPaid,
                'updated_by' => auth()->id(),
            ]);

            return redirect()->route('collections.index')
                ->with('success', "Collection recorded successfully! Receipt Number: {$receiptNumber}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to record collection. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified collection/payment details
     */
    /**
     * Display RD account details for collection
     */
    public function show(RDAccount $account)
    {
        // Eager load customer relationship with null check
        $account->load(['customer' => function($query) {
            $query->withDefault([
                'name' => 'N/A',
                'mobile_number' => 'N/A',
                'cif_id' => 'N/A'
            ]);
        }]);

        $payments = Payment::where('rd_account_id', $account->id)
            ->latest()
            ->limit(5)
            ->get();
            
        return view('agent.collections.show', [
            'account' => $account,
            'payments' => $payments,
            'customer' => $account->customer
        ]);
    }
}
