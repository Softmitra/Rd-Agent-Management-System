<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\RDAccount;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $agent = auth()->user();
        
        // RD Account Statistics
        $rdStats = [
            'active' => RDAccount::where('agent_id', $agent->id)
                ->where('status', 'active')
                ->count(),
            
            'completed' => RDAccount::where('agent_id', $agent->id)
                ->where('status', 'matured')
                ->count(),
            
            'pending' => RDAccount::where('agent_id', $agent->id)
                ->where('status', 'pending')
                ->count(),
            
            'defaulted' => RDAccount::where('agent_id', $agent->id)
                ->where('status', 'defaulted')
                ->count()
        ];

        $user = auth()->user();
        $data = [
            'user' => $user,
            'totalCustomers' => $user->customers()->count(),
            'totalAccounts' => $user->rdAccounts()->count(),
            'totalPayments' => $user->payments()->count(),
            'todayPayments' => $user->payments()->whereDate('created_at', today())->count(),
            'recentCustomers' => $user->customers()->latest()->take(5)->get(),
            'recentPayments' => $user->payments()->with('customer')->latest()->take(5)->get(),
            'rdStats' => $rdStats,
        ];
        return view('agent.dashboard', $data);
    }

    public function adminDashboard()
    {
        $user = auth()->user();
        $data = [
            'user' => $user,
            'totalAgents' => \App\Models\Agent::count(),
            'pendingVerifications' => \App\Models\Agent::where('is_verified', false)->count(),
            'totalCustomers' => \App\Models\Customer::count(),
            'totalAccounts' => \App\Models\RDAccount::count(),
            'recentAgents' => \App\Models\Agent::with('customers')->latest()->take(5)->get(),
        ];
        return view('admin.dashboard', $data);
    }
} 