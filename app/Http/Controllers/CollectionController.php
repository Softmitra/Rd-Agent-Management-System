<?php

namespace App\Http\Controllers;

use App\Exports\CollectionsExport;
use App\Models\Collection;
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
        $query = Payment::where('agent_id', $agent->id)
            ->with(['rdAccount', 'rdAccount.customer'])
            ->latest();

        if ($request->filled('account_number')) {
            $query->whereHas('rdAccount', function ($q) use ($request) {
                $q->where('account_number', 'like', '%' . $request->account_number . '%');
            });
        }

        if ($request->filled('customer_name')) {
            $query->whereHas('rdAccount.customer', function ($q) use ($request) {
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
                $q
                    ->where('name', 'like', "%$search%")
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

            $rdAccount = RDAccount::where('agent_id', auth()->id())
                ->where('status', 'active')
                ->first();

            if (!$rdAccount) {
                return redirect()
                    ->back()
                    ->with('error', 'No active RD account found for collection')
                    ->withInput();
            }

            Collection::create([
                'date' => $request->date,
                'payment_type' => $request->payment_type,
                'amount' => $request->amount,
                'note' => $request->note,
                'agent_id' => auth()->id(),
                'customer_id' => $rdAccount->customer_id,
                'status' => 'submitted'
            ]);

            return redirect()
                ->route('collections.index')
                ->with('success', 'Collection recorded successfully!');
        } catch (\Exception $e) {
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
}
