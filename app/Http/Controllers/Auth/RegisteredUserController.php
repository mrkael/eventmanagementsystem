<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterUserRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $user = User::create($request->validated());

        $staffRole = Role::where('key', 'staff')->first();
        $user->roles()->sync($staffRole ? [$staffRole->id] : []);

        event(new Registered($user));

        Auth::login($user);
        $auditLogger->record('auth.register', 'User registered a new account.', $user, [], $user->only(['name', 'email']));

        return redirect()->route('dashboard');
    }
}
