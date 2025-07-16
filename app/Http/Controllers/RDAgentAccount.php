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
        $query = RDAccount::withoutGlobalScopes()->with(['customer', 'agent']);

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

        return view('agent.rd-agent-accounts.index', compact('rdAccounts', 'summary'));
    }

    /**
     * Show the form for creating a new RD account.
     */
    public function create()
    {
        $customers = Customer::all();
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
                'monthly_amount' => 'required|numeric|min:0',
                'opening_date' => 'required|date_format:d/m/Y',
                'total_deposited' => 'required|numeric|min:0',
                'account_number' => 'required|string|unique:rd_accounts,account_number',
                'registered_phone' => 'required|string|size:10',
                'is_joint_account' => 'boolean',
                'joint_holder_name' => 'required_if:is_joint_account,1',
                'note' => 'nullable|string|max:500'
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

            // Get customer's agent_id
            $customer = Customer::findOrFail($validated['customer_id']);
            $validated['agent_id'] = $customer->agent_id;

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
                ->route('rd-agent-accounts.index')
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
        $rdAccount->load(['customer', 'agent', 'creator', 'updater']);
        return view('agent.rd-agent-accounts.show', compact('rdAccount'));
    }

    /**
     * Show the form for editing the specified RD account.
     */
    public function edit(RDAccount $rdAccount)
    {
        $customers = Customer::all();
        return view('rd-agent-accounts.edit', compact('rdAccount', 'customers'));
    }

    /**
     * Update the specified RD account in storage.
     */
    public function update(Request $request, RDAccount $rdAccount)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'monthly_amount' => 'required|numeric|min:0',
            'duration_months' => 'required|integer|min:1',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'status' => 'required|in:active,matured,closed',
            'remarks' => 'nullable|string|max:500',
        ]);

        // Recalculate maturity date and amount if relevant fields changed
        if ($request->start_date !== $rdAccount->start_date ||
                $request->duration_months !== $rdAccount->duration_months) {
            $validated['maturity_date'] = date('Y-m-d', strtotime($validated['start_date'] . " + {$validated['duration_months']} months"));
        }

        if ($request->monthly_amount !== $rdAccount->monthly_amount ||
                $request->duration_months !== $rdAccount->duration_months ||
                $request->interest_rate !== $rdAccount->interest_rate) {
            $validated['maturity_amount'] = $validated['monthly_amount'] * $validated['duration_months']
                * (1 + ($validated['interest_rate'] / 100));
        }

        $validated['updated_by'] = Auth::id();

        $rdAccount->update($validated);

        return redirect()
            ->route('rd-agent-accounts.index')
            ->with('success', 'RD Account updated successfully.');
    }

    /**
     * Remove the specified RD account from storage.
     */
    public function destroy(RDAccount $rdAccount)
    {
        $rdAccount->delete();
        return redirect()
            ->route('rd-agent-accounts.index')
            ->with('success', 'RD Account deleted successfully.');
    }

    /**
     * Close an RD account.
     */
    public function close(RDAccount $rdAccount)
    {
        if ($rdAccount->status === 'closed') {
            return back()->with('error', 'RD Account is already closed.');
        }

        $rdAccount->update([
            'status' => 'closed',
            'closed_at' => Carbon::now(),
        ]);

        return redirect()
            ->route('rd-agent-accounts.show', $rdAccount)
            ->with('success', 'RD Account closed successfully.');
    }

    /**
     * Mark an RD account as matured.
     */
    public function mature(RDAccount $rdAccount)
    {
        if ($rdAccount->status === 'matured') {
            return back()->with('error', 'RD Account is already marked as matured.');
        }

        $rdAccount->update([
            'status' => 'matured',
            'matured_at' => Carbon::now(),
        ]);

        return redirect()
            ->route('rd-agent-accounts.show', $rdAccount)
            ->with('success', 'RD Account marked as matured successfully.');
    }

    /**
     * Export RD accounts based on filters and format
     */
    public function export(Request $request)
    {
        $query = RDAccount::with(['customer', 'agent']);

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
        $fileName = 'rd_accounts_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new RDAccountsExport($rdAccounts), $fileName);
    }
}
