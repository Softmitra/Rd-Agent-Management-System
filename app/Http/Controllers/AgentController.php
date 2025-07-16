<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\User;
use App\Notifications\NewAgentRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AgentController extends Controller
{
    /**
     * Display a listing of the agents.
     */
    public function index()
    {
        $agents = Agent::with('verifier')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.agents.index', compact('agents'));
    }

    /**
     * Show the form for creating a new agent.
     */
    public function create()
    {
        return view('admin.agents.create');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:agents',
            'password' => 'required|string|min:8|confirmed',
            'contact_info' => 'nullable|array',
            'branch' => 'nullable|string|max:255',
        ]);

        $agent = Agent::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'contact_info' => $request->contact_info,
            'branch' => $request->branch,
        ]);

        $token = $agent->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Agent registered successfully',
            'agent' => $agent,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|string|size:10',
            'password' => 'required|string',
        ]);

        $agent = Agent::where('mobile_number', $request->mobile_number)->first();

        if (!$agent || !Hash::check($request->password, $agent->password)) {
            throw ValidationException::withMessages([
                'mobile_number' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$agent->is_verified) {
            throw ValidationException::withMessages([
                'mobile_number' => ['Your account is pending verification. Please contact the administrator.'],
            ]);
        }

        if (!$agent->is_active) {
            throw ValidationException::withMessages([
                'mobile_number' => ['Your account has been deactivated. Please contact the administrator.'],
            ]);
        }

        if ($agent->isExpired()) {
            throw ValidationException::withMessages([
                'mobile_number' => ['Your account has expired. Please contact the administrator.'],
            ]);
        }

        Auth::login($agent);

        $request->session()->regenerate();

        return redirect()->intended(route('agent.dashboard'));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'agent' => $request->user(),
        ]);
    }

    public function update(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:agents,email,' . $agent->id],
            'aadhar_number' => ['required', 'string', 'size:12', 'unique:agents,aadhar_number,' . $agent->id],
            'pan_number' => ['required', 'string', 'size:10', 'unique:agents,pan_number,' . $agent->id],
            'aadhar_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'pan_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'contact_info' => ['required', 'array'],
            'contact_info.phone' => ['required', 'string', 'max:15'],
            'contact_info.address' => ['required', 'string', 'max:500'],
            'branch' => ['required', 'string', 'max:255'],
        ]);

        // Handle file uploads if new files are provided
        if ($request->hasFile('aadhar_file')) {
            // Delete old file if it exists
            if ($agent->aadhar_file) {
                Storage::disk('public')->delete($agent->aadhar_file);
            }
            $validated['aadhar_file'] = $request->file('aadhar_file')
                ->store('agent-documents/aadhar', 'public');
        }

        if ($request->hasFile('pan_file')) {
            // Delete old file if it exists
            if ($agent->pan_file) {
                Storage::disk('public')->delete($agent->pan_file);
            }
            $validated['pan_file'] = $request->file('pan_file')
                ->store('agent-documents/pan', 'public');
        }

        $agent->update($validated);

        return redirect()->route('agents.show', $agent)
            ->with('success', 'Agent updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $agent = $request->user();

        if (!Hash::check($request->current_password, $agent->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $agent->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password changed successfully',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:agents'],
            'mobile_number' => ['required', 'string', 'size:10', 'unique:agents'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'aadhar_number' => ['required', 'string', 'size:12', 'unique:agents'],
            'pan_number' => ['required', 'string', 'size:10', 'unique:agents'],
            'aadhar_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'pan_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'contact_info' => ['required', 'array'],
            'contact_info.phone' => ['required', 'string', 'max:15'],
            'contact_info.address' => ['required', 'string', 'max:500'],
            'branch' => ['required', 'string', 'max:255'],
        ]);

        // Generate unique agent ID (AG followed by 8 random characters)
        $validated['agent_id'] = 'AG' . strtoupper(Str::random(8));
        
        // Handle file uploads
        if ($request->hasFile('aadhar_file')) {
            $validated['aadhar_file'] = $request->file('aadhar_file')
                ->store('agent-documents/aadhar', 'public');
        }
        
        if ($request->hasFile('pan_file')) {
            $validated['pan_file'] = $request->file('pan_file')
                ->store('agent-documents/pan', 'public');
        }

        $agent = Agent::create($validated);

        // Send notification to all admin users
        $adminUsers = User::all()->filter(function($user) {
            return $user->hasRole('admin');
        });
        foreach ($adminUsers as $admin) {
            $admin->notify(new NewAgentRegistered($agent));
        }

        return redirect()->route('agents.index')
            ->with('success', 'Agent created successfully. Pending verification.');
    }

    /**
     * Verify an agent.
     */
    public function verify(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $agent->verify(auth()->user(), $validated['remarks'] ?? null);

        // Send verification notification
        $agent->notify(new \App\Notifications\AgentVerified(
            auth()->user()->name,
            $validated['remarks'] ?? null
        ));

        return redirect()->route('agents.show', $agent)
            ->with('success', 'Agent verified successfully.');
    }

    /**
     * Unverify an agent.
     */
    public function unverify(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $agent->unverify($validated['remarks'] ?? null);

        // TODO: Send unverification email to agent

        return redirect()->route('agents.show', $agent)
            ->with('success', 'Agent unverified successfully.');
    }

    /**
     * Activate an agent account.
     */
    public function activate(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        $agent->activate(
            $validated['expires_at'] ? new \DateTime($validated['expires_at']) : null
        );

        // TODO: Send activation email to agent

        return redirect()->route('agents.show', $agent)
            ->with('success', 'Agent activated successfully.');
    }

    /**
     * Deactivate an agent account.
     */
    public function deactivate(Agent $agent)
    {
        $agent->deactivate();

        // TODO: Send deactivation email to agent

        return redirect()->route('agents.show', $agent)
            ->with('success', 'Agent deactivated successfully.');
    }

    /**
     * Update an agent's account expiration date.
     */
    public function updateExpiration(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        $agent->update([
            'account_expires_at' => $validated['expires_at'] ? new \DateTime($validated['expires_at']) : null,
        ]);

        // Clear any session data related to account expiration
        session()->forget('account_status');

        return redirect()->route('agents.show', $agent)
            ->with('success', 'Agent expiration date updated successfully.');
    }

    /**
     * Display the specified agent.
     */
    public function show(Agent $agent)
    {
        $agent->load('verifier');
        return view('admin.agents.show', compact('agent'));
    }

    /**
     * Show the form for editing the specified agent.
     */
    public function edit(Agent $agent)
    {
        return view('admin.agents.edit', compact('agent'));
    }
}
