<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => request()->user()->load('department', 'roles'),
        ]);
    }

    public function update(ProfileUpdateRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $user = $request->user();
        $oldValues = $user->only(['name', 'email', 'phone', 'position']);
        $data = $request->validated();

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'position' => $data['position'] ?? null,
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $auditLogger->record('profile.update', 'Updated own profile.', $user, $oldValues, $user->only(['name', 'email', 'phone', 'position']));

        return back()->with('status', 'Profile updated successfully.');
    }
}
