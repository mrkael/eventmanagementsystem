<x-layouts.admin title="Departments" heading="Department Management" subheading="Maintain organization units used by users and events">
    <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-5 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" class="flex flex-1 flex-col gap-3 sm:flex-row">
                <input name="search" value="{{ request('search') }}" placeholder="Search name or code" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                <select name="status" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                    <option value="">All statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button class="btn btn-outline-primary btn-md">Filter</button>
            </form>
            @if (auth()->user()->hasPermission('departments.create'))
                <a href="{{ route('admin.departments.create') }}" class="btn btn-primary btn-md">New department</a>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Department</th><th class="px-5 py-3">Code</th><th class="px-5 py-3">Users</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($departments as $department)
                        <tr>
                            <td class="px-5 py-4"><a href="{{ route('admin.departments.show', $department) }}" class="font-semibold text-slate-900 hover:text-emerald-700">{{ $department->name }}</a><p class="mt-1 max-w-xl text-slate-500">{{ $department->description }}</p></td>
                            <td class="px-5 py-4 font-medium text-slate-700">{{ $department->code }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $department->users_count }}</td>
                            <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $department->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $department->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" onsubmit="return confirm('Delete this department?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No departments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-5">{{ $departments->links() }}</div>
    </section>
</x-layouts.admin>
