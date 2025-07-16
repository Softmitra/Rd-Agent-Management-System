<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Agent;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // Attempt to login with the provided credentials
        if (Auth::attempt($this->credentials($request), $request->boolean('remember'))) {
            $user = Auth::user();
            
            // Check if agent is verified and active
            if (!$user->is_verified) {
                Auth::logout();
                return back()->withErrors(['mobile_number' => 'Your account is pending verification. Please contact the administrator.']);
            }
            
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['mobile_number' => 'Your account has been deactivated. Please contact the administrator.']);
            }
            
            // Store user ID in session
            $request->session()->put('user_id', $user->id);
            
            // Regenerate session
            $request->session()->regenerate();
            
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful, redirect back with an error message
        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|string|size:10',
            'password' => 'required|string',
        ]);
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return [
            'mobile_number' => $request->mobile_number,
            'password' => $request->password,
        ];
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        }
        
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'mobile_number' => [trans('auth.failed')],
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }

    protected function authenticated(Request $request, $user)
    {
        // Store user ID in session
        $request->session()->put('user_id', $user->id);
        
        // Clear any existing session data
        $request->session()->regenerate();
        
        return redirect()->intended($this->redirectPath());
    }
}
