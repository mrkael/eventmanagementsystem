<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with(['department', 'roles'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('department_id'), fn ($query) => $query->where('department_id', $request->department_id))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'departments' => Department::orderBy('name')->get(),
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', $this->formData());
    }

    public function store(UserRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'department_id' => $data['department_id'] ?? null,
                'phone' => $data['phone'] ?? null,
                'position' => $data['position'] ?? null,
                'status' => $data['status'],
            ]);

            $user->roles()->sync($data['role_ids'] ?? []);

            return $user;
        });

        $auditLogger->record('users.create', "Created user {$user->email}.", $user, [], $user->load('roles', 'department')->toArray());

        return redirect()->route('admin.users.index')->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            ...$this->formData(),
        ]);
    }

    public function update(UserRequest $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        $oldValues = $user->load('roles', 'department')->toArray();
        $data = $request->validated();

        DB::transaction(function () use ($data, $user) {
            $user->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'department_id' => $data['department_id'] ?? null,
                'phone' => $data['phone'] ?? null,
                'position' => $data['position'] ?? null,
                'status' => $data['status'],
            ]);

            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();
            $user->roles()->sync($data['role_ids'] ?? []);
        });

        $auditLogger->record('users.update', "Updated user {$user->email}.", $user, $oldValues, $user->fresh()->load('roles', 'department')->toArray());

        return redirect()->route('admin.users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(User $user, AuditLogger $auditLogger): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $oldValues = $user->load('roles', 'department')->toArray();
        $user->delete();

        $auditLogger->record('users.delete', "Deleted user {$user->email}.", $user, $oldValues);

        return redirect()->route('admin.users.index')->with('status', 'User deleted successfully.');
    }

    private function formData(): array
    {
        return [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'roles' => Role::orderBy('name')->get(),
            'statuses' => UserStatus::cases(),
        ];
    }
}
