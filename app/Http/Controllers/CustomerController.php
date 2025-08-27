<?php

namespace App\Http\Controllers;

use App\Exports\CustomersExport;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $query = Customer::with(['agent', 'rdAccounts']);

        // Apply filters
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->filled('has_rd_account')) {
            if ($request->has_rd_account === 'yes') {
                $query->has('rdAccounts');
            } elseif ($request->has_rd_account === 'no') {
                $query->doesntHave('rdAccounts');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('aadhar_number', 'like', "%{$search}%")
                    ->orWhere('pan_number', 'like', "%{$search}%")
                    ->orWhereJsonContains('contact_info->phone', $search);
            });
        }

        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('created_at', [
                    \Carbon\Carbon::parse($dates[0])->startOfDay(),
                    \Carbon\Carbon::parse($dates[1])->endOfDay()
                ]);
            }
        }

        // Get summary data
        $summary = [
            'total_customers' => $query->count(),
            'with_rd_accounts' => Customer::has('rdAccounts')->count(),
            'without_rd_accounts' => Customer::doesntHave('rdAccounts')->count(),
            'total_rd_accounts' => \App\Models\RDAccount::count(),
            'recent_customers' => Customer::whereBetween('created_at', [now()->subDays(30), now()])->count()
        ];

        // Get paginated results
        $customers = $query
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        $agents = \App\Models\Agent::where('is_active', true)
            ->where('is_verified', true)
            ->get(['id', 'name']);

        return view('admin.customers.index', compact('customers', 'agents', 'summary'));
    }

    /**
     * Show the form for creating a new customer.
     */
    // public function create()
    // {
    //     return view('admin.customers.create');
    // }
    public function create()
    {
        // Check if logged in user is an agent
        $isAgent = auth()->guard('web')->check() && auth()->user() instanceof \App\Models\Agent;
        
        // Always fetch all agents regardless of admin/agent status
        $agents = \App\Models\Agent::where('is_active', true)
            ->where('is_verified', true)
            ->get(['id', 'name', 'mobile_number', 'agent_id']);

        // Log the result
        Log::info('Fetched agents for customer creation', ['agents' => $agents]);

        return view('admin.customers.create', compact('agents'));
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request)
    {
        // Check if logged in user is an agent
        $isAgent = auth()->guard('web')->check() && auth()->user() instanceof \App\Models\Agent;
        
        // Validation rules - agent_id is always required
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers'],
            'mobile_number' => ['required', 'string', 'size:10', 'unique:customers'],
            'phone' => ['required', 'string', 'size:10', 'unique:customers'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'cif_id' => ['nullable', 'string', 'max:50', 'unique:customers'],
            'address' => ['required', 'string', 'max:1000'],
            'agent_id' => ['required', 'exists:agents,id'], // Always require agent_id
        ];

        $validated = $request->validate($validationRules);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('customer-photos', 'public');
        }

        $customer = Customer::create($validated);

        Log::info('New customer created', [
            'customer_id' => $customer->id,
            'agent_id' => $validated['agent_id']
        ]);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        $customer->load(['agent', 'rdAccounts']);
        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email,' . $customer->id],
            'mobile_number' => ['required', 'string', 'size:10', 'unique:customers,mobile_number,' . $customer->id],
            'phone' => ['required', 'string', 'size:10', 'unique:customers,phone,' . $customer->id],
            // 'aadhar_number' => ['nullable', 'string', 'size:12', 'unique:customers,aadhar_number,' . $customer->id],
            // 'pan_number' => ['nullable', 'string', 'size:10', 'unique:customers,pan_number,' . $customer->id],
            // 'aadhar_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            // 'pan_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            // 'has_savings_account' => ['nullable', 'boolean'],
            'cif_id' => ['nullable', 'string', 'max:50', 'unique:customers,cif_id,' . $customer->id],
            // 'savings_account_no' => ['nullable', 'string', 'max:50', 'unique:customers,savings_account_no,' . $customer->id],
        ]);

        // Handle file uploads if new files are provided
        if ($request->hasFile('aadhar_file')) {
            // Delete old file if it exists
            if ($customer->aadhar_file) {
                Storage::disk('public')->delete($customer->aadhar_file);
            }
            $validated['aadhar_file'] = $request
                ->file('aadhar_file')
                ->store('customer-documents/aadhar', 'public');
        }

        if ($request->hasFile('pan_file')) {
            // Delete old file if it exists
            if ($customer->pan_file) {
                Storage::disk('public')->delete($customer->pan_file);
            }
            $validated['pan_file'] = $request
                ->file('pan_file')
                ->store('customer-documents/pan', 'public');
        }

        if ($request->hasFile('photo')) {
            // Delete old file if it exists
            if ($customer->photo) {
                Storage::disk('public')->delete($customer->photo);
            }
            $validated['photo'] = $request
                ->file('photo')
                ->store('customer-photos', 'public');
        }

        $customer->update($validated);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer)
    {
        // Delete associated files
        if ($customer->aadhar_file) {
            Storage::disk('public')->delete($customer->aadhar_file);
        }
        if ($customer->pan_file) {
            Storage::disk('public')->delete($customer->pan_file);
        }
        if ($customer->photo) {
            Storage::disk('public')->delete($customer->photo);
        }

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Export customers data to Excel
     */
    public function export(Request $request)
    {
        $query = Customer::with(['agent', 'rdAccounts']);

        // Apply filters
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->filled('has_rd_account')) {
            if ($request->has_rd_account === 'yes') {
                $query->has('rdAccounts');
            } elseif ($request->has_rd_account === 'no') {
                $query->doesntHave('rdAccounts');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('created_at', [
                    \Carbon\Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay(),
                    \Carbon\Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay()
                ]);
            }
        }

        $customers = $query->get();

        return Excel::download(new CustomersExport($customers), 'customers-' . date('Y-m-d') . '.xlsx');
    }
    
    /**
     * Get RD accounts for a specific customer (API endpoint for Excel import duplicate checking)
     */
    public function getRdAccounts(Customer $customer)
    {
        $rdAccounts = $customer->rdAccounts()->select([
            'id', 
            'account_number', 
            'monthly_amount', 
            'status', 
            'start_date', 
            'maturity_date'
        ])->get();
        
        return response()->json($rdAccounts);
    }

    public function getAgent($id)
    {
        $customer = \App\Models\Customer::with('agent')->findOrFail($id);

        Log::info('Fetching agent for customer', [
            'customer_id' => $customer->id,
            'agent_id' => $customer->agent_id,
            'agent_name' => optional($customer->agent)->name,
        ]);

        return response()->json([
            'agent_id' => $customer->agent_id,
            'agent_name' => optional($customer->agent)->name,
        ]);
    }
}
