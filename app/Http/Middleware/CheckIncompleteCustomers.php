<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use Symfony\Component\HttpFoundation\Response;

class CheckIncompleteCustomers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for agents (not admins)
        if (!Auth::check() || !Auth::user() instanceof \App\Models\Agent) {
            return $next($request);
        }

        $agent = Auth::user();
        
        // Get count of incomplete customers for this agent
        $incompleteCount = Customer::where('agent_id', $agent->id)
            ->incomplete()
            ->count();

        if ($incompleteCount > 0) {
            // Check if this is an AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Incomplete customer information',
                    'message' => "You have {$incompleteCount} customers with incomplete information. Please complete their details before proceeding with lot/collection operations.",
                    'incomplete_count' => $incompleteCount,
                    'redirect_url' => route('agent.customers.index')
                ], 422);
            }

            // For regular requests, redirect with error message
            return redirect()->route('agent.customers.index')
                ->with('error', "You have {$incompleteCount} customers with incomplete information. Please complete their details before proceeding with lot/collection operations.");
        }

        return $next($request);
    }
}
