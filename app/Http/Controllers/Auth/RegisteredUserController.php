<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:agents'],
            'mobile_number' => ['required', 'string', 'size:10', 'unique:agents'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'agent_id' => ['required', 'string', 'max:255', 'unique:agents'],
            'aadhar_number' => ['required', 'string', 'size:12', 'unique:agents'],
            'pan_number' => ['required', 'string', 'size:10', 'unique:agents'],
            'aadhar_image' => ['nullable', 'image', 'max:2048'],
            'pan_image' => ['nullable', 'image', 'max:2048'],
            'branch' => ['nullable', 'string', 'max:255'],
            'contact_info' => ['nullable', 'array'],
            'contact_info.address' => ['nullable', 'string', 'max:500'],
        ]);

        $agent = Agent::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'password' => Hash::make($request->password),
            'agent_id' => $request->agent_id,
            'aadhar_number' => $request->aadhar_number,
            'pan_number' => $request->pan_number,
            'branch' => $request->branch,
            'contact_info' => [
                'address' => $request->input('contact_info.address'),
            ],
        ]);

        // Handle file uploads if provided
        if ($request->hasFile('aadhar_image')) {
            $agent->aadhar_file = $request->file('aadhar_image')->store('agent-documents/aadhar', 'public');
            $agent->save();
        }

        if ($request->hasFile('pan_image')) {
            $agent->pan_file = $request->file('pan_image')->store('agent-documents/pan', 'public');
            $agent->save();
        }

        event(new Registered($agent));

        Auth::login($agent);

        return redirect(RouteServiceProvider::HOME);
    }
} 