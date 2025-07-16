<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAgentAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // For admin routes
        if ($request->is('admin/*')) {
            if (!$request->user()->hasRole('admin')) {
                return redirect()->route('dashboard')
                    ->with('error', 'You do not have admin access.');
            }
            return $next($request);
        }

        // For agent routes
        if ($request->is('agent/*') || $request->is('dashboard')) {
            if (!$request->user()->canAccess()) {
                if (!$request->user()->is_verified) {
                    return redirect()->route('verification.notice')
                        ->with('error', 'Your account is pending verification.');
                }
                
                if (!$request->user()->is_active) {
                    auth()->logout();
                    return redirect()->route('login')
                        ->with('error', 'Your account has been deactivated.');
                }
                
                // We're not checking for expiration at login time anymore
                // The popup will be shown on the dashboard if the account is expired
            }
        }

        return $next($request);
    }
}
