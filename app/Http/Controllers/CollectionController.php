<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\RDAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $agent = Auth::user();
        $query = Payment::where('agent_id', $agent->id)
            ->with(['rdAccount', 'rdAccount.customer'])
            ->latest();

        if ($request->filled('account_number')) {
            $query->whereHas('rdAccount', function($q) use ($request) {
                $q->where('account_number', 'like', '%'.$request->account_number.'%');
            });
        }

        if ($request->filled('customer_name')) {
            $query->whereHas('rdAccount.customer', function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->customer_name.'%');
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

            $query->whereHas('customer', function($q) use ($search) {
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
                'date' => 'required|date',
                'payment_type' => 'required|in:cash,upi',
                'amount' => 'required|numeric|min:0',
                'note' => 'nullable|string',
            ]);

            // Get the selected RD account from search results
            $rdAccount = RDAccount::where('agent_id', auth()->id())
                ->where('status', 'active')
                ->first();

            if (!$rdAccount) {
                return redirect()->back()
                    ->with('error', 'No active RD account found for collection')
                    ->withInput();
            }

            // Create collection record
            $collection = \App\Models\Collection::create([
                'date' => $request->date,
                'payment_type' => $request->payment_type,
                'amount' => $request->amount,
                'note' => $request->note,
                'agent_id' => auth()->id(),
                'customer_id' => $rdAccount->customer_id,
                'status' => 'submitted'
            ]);

            return redirect()->route('collections.index')
                ->with('success', 'Collection recorded successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to record collection: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $account = RDAccount::with(['customer' => function($query) {
                $query->withDefault([
                    'name' => 'N/A',
                    'mobile_number' => 'N/A',
                    'cif_id' => 'N/A'
                ]);
            }])->findOrFail($id);

            Log::info('Showing RDAccount', ['account_id' => $account->id]);

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
            Log::error('Failed to show RD account', [
                'error' => $e->getMessage(),
                'account_id' => $id ?? null
            ]);
            
            return redirect()->route('collections.create')
                ->with('error', 'Invalid RD Account. Please try again.');
        }
    }
}
