<x-layouts.admin title="Department Detail" heading="{{ $department->name }}" subheading="Department record">
    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <dl class="grid gap-5 sm:grid-cols-2">
            <div><dt class="text-sm font-medium text-slate-500">Code</dt><dd class="mt-1 font-semibold">{{ $department->code }}</dd></div>
            <div><dt class="text-sm font-medium text-slate-500">Status</dt><dd class="mt-1">{{ $department->is_active ? 'Active' : 'Inactive' }}</dd></div>
            <div><dt class="text-sm font-medium text-slate-500">Assigned users</dt><dd class="mt-1">{{ $department->users_count }}</dd></div>
            <div><dt class="text-sm font-medium text-slate-500">Created</dt><dd class="mt-1">{{ $department->created_at->format('d M Y') }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-sm font-medium text-slate-500">Description</dt><dd class="mt-1 text-slate-700">{{ $department->description ?: 'No description provided.' }}</dd></div>
        </dl>
        <div class="mt-6 flex gap-3">
            <a href="{{ route('admin.departments.edit', $department) }}" class="inline-flex min-h-11 items-center rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800">Edit</a>
            <a href="{{ route('admin.departments.index') }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back</a>
        </div>
    </section>
</x-layouts.admin>
