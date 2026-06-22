<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $departments = Department::query()
            ->withCount('users')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', "%{$request->search}%")
                        ->orWhere('code', 'like', "%{$request->search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->status === 'active'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.departments.index', compact('departments'));
    }

    public function create(): View
    {
        return view('admin.departments.create');
    }

    public function store(DepartmentRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $department = Department::create($data);

        $auditLogger->record('departments.create', "Created department {$department->name}.", $department, [], $department->toArray());

        return redirect()->route('admin.departments.index')->with('status', 'Department created successfully.');
    }

    public function show(Department $department): View
    {
        $department->loadCount('users');

        return view('admin.departments.show', compact('department'));
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(DepartmentRequest $request, Department $department, AuditLogger $auditLogger): RedirectResponse
    {
        $oldValues = $department->toArray();

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $department->update($data);

        $auditLogger->record('departments.update', "Updated department {$department->name}.", $department, $oldValues, $department->fresh()->toArray());

        return redirect()->route('admin.departments.index')->with('status', 'Department updated successfully.');
    }

    public function destroy(Department $department, AuditLogger $auditLogger): RedirectResponse
    {
        if ($department->users()->exists()) {
            return back()->withErrors(['department' => 'This department is assigned to users and cannot be deleted.']);
        }

        $oldValues = $department->toArray();
        $department->delete();

        $auditLogger->record('departments.delete', "Deleted department {$department->name}.", $department, $oldValues);

        return redirect()->route('admin.departments.index')->with('status', 'Department deleted successfully.');
    }
}
