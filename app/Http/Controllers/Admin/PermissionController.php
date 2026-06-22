<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use App\Models\Permission;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        $permissions = Permission::query()
            ->withCount('roles')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('key', 'like', "%{$request->search}%")
                    ->orWhere('group', 'like', "%{$request->search}%");
            })
            ->when($request->filled('group'), fn ($query) => $query->where('group', $request->group))
            ->orderBy('group')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $groups = Permission::query()->distinct()->orderBy('group')->pluck('group');

        return view('admin.permissions.index', compact('permissions', 'groups'));
    }

    public function create(): View
    {
        return view('admin.permissions.create');
    }

    public function store(PermissionRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $permission = Permission::create($request->validated());

        $auditLogger->record('permissions.create', "Created permission {$permission->key}.", $permission, [], $permission->toArray());

        return redirect()->route('admin.permissions.index')->with('status', 'Permission created successfully.');
    }

    public function edit(Permission $permission): View
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(PermissionRequest $request, Permission $permission, AuditLogger $auditLogger): RedirectResponse
    {
        $oldValues = $permission->toArray();
        $permission->update($request->validated());

        $auditLogger->record('permissions.update', "Updated permission {$permission->key}.", $permission, $oldValues, $permission->fresh()->toArray());

        return redirect()->route('admin.permissions.index')->with('status', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission, AuditLogger $auditLogger): RedirectResponse
    {
        if ($permission->roles()->exists()) {
            return back()->withErrors(['permission' => 'This permission is assigned to roles and cannot be deleted.']);
        }

        $oldValues = $permission->toArray();
        $permission->delete();

        $auditLogger->record('permissions.delete', "Deleted permission {$permission->key}.", $permission, $oldValues);

        return redirect()->route('admin.permissions.index')->with('status', 'Permission deleted successfully.');
    }
}
