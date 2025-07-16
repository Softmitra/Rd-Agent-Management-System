<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\RDAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use PDF;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['rdAccount', 'rdAccount.customer'])
            ->latest();

        // Apply filters if any
        if ($request->filled('account_number')) {
            $query->whereHas('rdAccount', function($q) use ($request) {
                $q->where('account_number', 'like', '%' . $request->account_number . '%');
            });
        }

        if ($request->filled('payment_date')) {
            $query->whereDate('payment_date', $request->payment_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->paginate(10)
            ->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request)
    {
        $rdAccount = null;
        if ($request->has('rd_account_id')) {
            $rdAccount = RDAccount::with('customer')->findOrFail($request->rd_account_id);
        }
        
        return view('admin.payments.create', compact('rdAccount'));
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rd_account_id' => 'required|exists:rd_accounts,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,upi,bank_transfer,cheque',
            'transaction_id' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:500',
        ]);

        $rdAccount = RDAccount::findOrFail($validated['rd_account_id']);

        // Verify payment amount matches monthly amount
        if ($validated['amount'] != $rdAccount->monthly_amount) {
            return back()
                ->withInput()
                ->withErrors(['amount' => 'Payment amount must match the monthly deposit amount.']);
        }

        // Add receipt number
        $validated['receipt_number'] = 'RCPT' . date('Ymd') . str_pad(Payment::count() + 1, 6, '0', STR_PAD_LEFT);
        $validated['status'] = 'completed';
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $payment = Payment::create($validated);

        return redirect()
            ->route('payments.show', $payment)
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $payment->load(['rdAccount', 'rdAccount.customer', 'creator', 'updater']);
        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(Payment $payment)
    {
        return view('admin.payments.edit', compact('payment'));
    }

    /**
     * Update the specified payment in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,upi,bank_transfer,cheque',
            'transaction_id' => 'nullable|string|max:100',
            'status' => 'required|in:pending,completed,failed',
            'remarks' => 'nullable|string|max:500',
        ]);

        $validated['updated_by'] = Auth::id();
        
        $payment->update($validated);

        return redirect()
            ->route('payments.show', $payment)
            ->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the specified payment from storage.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()
            ->route('payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    /**
     * Generate receipt for the payment
     */
    public function receipt(Payment $payment)
    {
        // Verify that the authenticated user has access to this payment
        if (auth()->user()->role === 'agent' && $payment->agent_id !== auth()->id()) {
            abort(403, 'Unauthorized access to payment receipt.');
        }

        // Load the payment with its relationships
        $payment->load(['rdAccount', 'customer', 'agent']);

        // Generate PDF
        $pdf = PDF::loadView('payments.receipt', compact('payment'));

        // Return the PDF for download
        return $pdf->download('payment-receipt-' . $payment->receipt_number . '.pdf');
    }

    /**
     * Show pending payments for an RD account
     */
    public function pending(RDAccount $rdAccount)
    {
        $startDate = $rdAccount->start_date;
        $endDate = now()->endOfMonth();
        $expectedPayments = [];
        
        // Generate list of expected payment dates
        while ($startDate <= $endDate) {
            $paymentDate = $startDate->copy();
            $payment = $rdAccount->payments()
                ->whereDate('payment_date', $paymentDate)
                ->first();

            $expectedPayments[] = [
                'due_date' => $paymentDate->format('Y-m-d'),
                'amount' => $rdAccount->monthly_amount,
                'status' => $payment ? 'paid' : 'pending',
                'payment' => $payment,
            ];

            $startDate->addMonth();
        }

        return view('admin.payments.pending', compact('rdAccount', 'expectedPayments'));
    }
} 