<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\RDAccount;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of agent's customers
     */
    public function index()
    {
        // Get current authenticated agent
        $agent = Auth::user();
        
        // Get customers assigned to this agent with their RD accounts
        $customers = Customer::where('agent_id', $agent->id)
            ->with(['rdAccounts', 'agent'])
            ->paginate(15);
            
        // Get customer counts using the new completion logic
        $totalCustomers = Customer::where('agent_id', $agent->id)->count();
        $incompleteCustomers = Customer::where('agent_id', $agent->id)->incomplete()->count();
        $completeCustomers = $totalCustomers - $incompleteCustomers;
            
        return view('agent.customers.index', compact('customers', 'incompleteCustomers', 'totalCustomers', 'completeCustomers'));
    }

    /**
     * Show the form for creating a new customer
     */
    public function create()
    {
        // Get current authenticated agent
        $agent = Auth::user();
        
        return view('agent.customers.create', compact('agent'));
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        // Get current authenticated agent
        $agent = Auth::user();
        
        // Debug logging
        \Log::info('Agent Store - Current User:', [
            'user_id' => $agent ? $agent->id : null,
            'user_name' => $agent ? $agent->name : null,
            'user_email' => $agent ? $agent->email : null,
            'is_agent_model' => $agent instanceof \App\Models\Agent,
            'auth_guard' => Auth::getDefaultDriver()
        ]);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:15|unique:customers',
            'phone' => 'nullable|string|max:15',
            'email' => 'nullable|email|unique:customers',
            'address' => 'nullable|string',
            'aadhar_number' => 'nullable|string|max:12|unique:customers',
            'pan_number' => 'nullable|string|max:10|unique:customers',
            'date_of_birth' => 'nullable|date',
            'has_savings_account' => 'boolean',
            'savings_account_no' => 'nullable|string|unique:customers',
        ]);
        
        $customer = Customer::create([
            'name' => $request->name,
            'mobile_number' => $request->mobile_number,
            'phone' => $request->phone ?? $request->mobile_number,
            'email' => $request->email,
            'address' => $request->address,
            'aadhar_number' => $request->aadhar_number,
            'pan_number' => $request->pan_number,
            'date_of_birth' => $request->date_of_birth,
            'agent_id' => $agent->id,
            'has_savings_account' => $request->has_savings_account ?? false,
            'savings_account_no' => $request->savings_account_no,
        ]);
        
        return redirect()->route('agent.customers.show', $customer)
                        ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified customer
     */
    public function show(Customer $customer)
    {
        // Get current authenticated agent
        $agent = Auth::user();
        
        // Ensure customer belongs to this agent
        if ($customer->agent_id !== $agent->id) {
            abort(403, 'Unauthorized access to customer data.');
        }
        
        $customer->load(['rdAccounts', 'payments']);
        
        return view('agent.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the customer
     */
    public function edit(Customer $customer)
    {
        // Get current authenticated agent
        $agent = Auth::user();
        
        // Ensure customer belongs to this agent
        if ($customer->agent_id !== $agent->id) {
            abort(403, 'Unauthorized access to customer data.');
        }
        
        return view('agent.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, Customer $customer)
    {
        // Get current authenticated agent
        $agent = Auth::user();
        
        // Ensure customer belongs to this agent
        if ($customer->agent_id !== $agent->id) {
            abort(403, 'Unauthorized access to customer data.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile_number' => ['required', 'string', 'max:15', Rule::unique('customers')->ignore($customer->id)],
            'phone' => 'nullable|string|max:15',
            'email' => ['nullable', 'email', Rule::unique('customers')->ignore($customer->id)],
            'address' => 'nullable|string',
            'aadhar_number' => ['nullable', 'string', 'max:12', Rule::unique('customers')->ignore($customer->id)],
            'pan_number' => ['nullable', 'string', 'max:10', Rule::unique('customers')->ignore($customer->id)],
            'date_of_birth' => 'nullable|date',
            'has_savings_account' => 'boolean',
            'savings_account_no' => ['nullable', 'string', Rule::unique('customers')->ignore($customer->id)],
        ]);
        
        $customer->update([
            'name' => $request->name,
            'mobile_number' => $request->mobile_number,
            'phone' => $request->phone ?? $request->mobile_number,
            'email' => $request->email,
            'address' => $request->address,
            'aadhar_number' => $request->aadhar_number,
            'pan_number' => $request->pan_number,
            'date_of_birth' => $request->date_of_birth,
            'has_savings_account' => $request->has_savings_account ?? false,
            'savings_account_no' => $request->savings_account_no,
        ]);
        
        return redirect()->route('agent.customers.show', $customer)
                        ->with('success', 'Customer updated successfully.');
    }

    /**
     * Get incomplete customers for completion modal
     */
    public function getIncompleteCustomers()
    {
        // Get current authenticated agent
        $agent = Auth::user();
        
        $incompleteCustomers = Customer::where('agent_id', $agent->id)
            ->incomplete()
            ->with('rdAccounts')
            ->limit(10) // Show only first 10 for completion
            ->get();
            
        return response()->json([
            'customers' => $incompleteCustomers,
            'count' => $incompleteCustomers->count()
        ]);
    }
    
    /**
     * Complete customer details
     */
    public function completeCustomer(Request $request, Customer $customer)
    {
        // Get current authenticated agent
        $agent = Auth::user();
        
        // Ensure customer belongs to this agent
        if ($customer->agent_id !== $agent->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'mobile_number' => 'required|string|max:15',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'email' => 'nullable|email',
            'cif_id' => 'required|string|max:255',
            'aadhar_number' => 'nullable|string|max:12',
            'pan_number' => 'nullable|string|max:10',
            'date_of_birth' => 'nullable|date',
            'has_savings_account' => 'boolean',
            'savings_account_no' => 'nullable|string',
        ]);
        
        // Apply same logic as customer creation
        // Auto-assign mobile_number to phone if phone is not provided
        $validated['phone'] = $validated['phone'] ?? $validated['mobile_number'];
        
        // Auto-set has_savings_account to false if not provided (same as creation logic)
        $validated['has_savings_account'] = $validated['has_savings_account'] ?? false;
        
        // Set data_source if not already set (track completion via modal)
        if (empty($customer->data_source)) {
            $validated['data_source'] = 'manual';
        }
        
        $customer->update($validated);
        
        // Mark customer as complete if information is sufficient
        if ($customer->isInfoComplete()) {
            $customer->markAsComplete('Completed via completion modal');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Customer details completed successfully.',
            'customer' => $customer
        ]);
    }
}
