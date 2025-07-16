<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['agent' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $agent = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:agents,email,' . $agent->id,
            'contact_info' => 'nullable|array',
            'branch' => 'nullable|string|max:255',
        ]);

        $agent->update($request->only(['name', 'email', 'contact_info', 'branch']));

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $agent = auth()->user();

        if (!Hash::check($request->current_password, $agent->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $agent->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Password changed successfully');
    }
} 