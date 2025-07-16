<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AccountExpired;
use App\Notifications\AccountExpiringSoon;
use Carbon\Carbon;

class CheckAccountExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !$request->user()->hasRole('admin')) {
            $agent = Auth::user();
            
            // Check if account is expired
            if ($agent->isExpired()) {
                // Send expired notification if not already sent
                if (!$agent->notifications()->where('type', AccountExpired::class)->exists()) {
                    $agent->notify(new AccountExpired($agent->account_expires_at->format('d M Y')));
                }
                
                // Store expiration status in session
                session([
                    'account_status' => [
                        'type' => 'expired',
                        'message' => 'Your account has expired. Please contact the administrator to reactivate your account.',
                        'expired_at' => $agent->account_expires_at->format('d M Y'),
                    ]
                ]);
            } 
            // Check if account is expiring soon (within 7 days)
            elseif ($agent->account_expires_at && $agent->account_expires_at->diffInDays(now()) <= 7) {
                // Send expiring soon notification if not already sent
                if (!$agent->notifications()->where('type', AccountExpiringSoon::class)->exists()) {
                    $agent->notify(new AccountExpiringSoon(
                        $agent->account_expires_at->diffInDays(now()),
                        $agent->account_expires_at->format('d M Y')
                    ));
                }
                
                // Store expiration status in session
                session([
                    'account_status' => [
                        'type' => 'expiring_soon',
                        'message' => 'Your account will expire on ' . $agent->account_expires_at->format('d M Y') . '. Please contact the administrator to extend your account.',
                        'expires_at' => $agent->account_expires_at->format('d M Y'),
                        'days_remaining' => $agent->account_expires_at->diffInDays(now()),
                    ]
                ]);
            } else {
                // If account is not expired and not expiring soon, clear the session data
                if (session()->has('account_status')) {
                    session()->forget('account_status');
                }
            }
        }
        
        return $next($request);
    }
} 