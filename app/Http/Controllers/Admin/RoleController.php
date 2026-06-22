<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('key', 'like', "%{$request->search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.roles.create', [
            'permissionsByGroup' => Permission::orderBy('group')->orderBy('name')->get()->groupBy('group'),
        ]);
    }

    public function store(RoleRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();

        $role = Role::create([
            'name' => $data['name'],
            'key' => Str::slug($data['key']),
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($data['permission_ids'] ?? []);

        $auditLogger->record('roles.create', "Created role {$role->name}.", $role, [], $role->load('permissions')->toArray());

        return redirect()->route('admin.roles.index')->with('status', 'Role created successfully.');
    }

    public function show(Role $role): View
    {
        $role->load('permissions')->loadCount('users');

        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissionsByGroup' => Permission::orderBy('group')->orderBy('name')->get()->groupBy('group'),
        ]);
    }

    public function update(RoleRequest $request, Role $role, AuditLogger $auditLogger): RedirectResponse
    {
        $oldValues = $role->load('permissions')->toArray();
        $data = $request->validated();

        $role->update([
            'name' => $data['name'],
            'key' => $role->is_system ? $role->key : Str::slug($data['key']),
            'description' => $data['description'] ?? null,
            'is_system' => $role->is_system,
        ]);

        $role->permissions()->sync($data['permission_ids'] ?? []);

        $auditLogger->record('roles.update', "Updated role {$role->name}.", $role, $oldValues, $role->fresh()->load('permissions')->toArray());

        return redirect()->route('admin.roles.index')->with('status', 'Role updated successfully.');
    }

    public function destroy(Role $role, AuditLogger $auditLogger): RedirectResponse
    {
        if ($role->is_system || $role->users()->exists()) {
            return back()->withErrors(['role' => 'System roles and roles assigned to users cannot be deleted.']);
        }

        $oldValues = $role->load('permissions')->toArray();
        $role->delete();

        $auditLogger->record('roles.delete', "Deleted role {$role->name}.", $role, $oldValues);

        return redirect()->route('admin.roles.index')->with('status', 'Role deleted successfully.');
    }
}
