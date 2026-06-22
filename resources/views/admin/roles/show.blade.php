<x-layouts.admin title="Role Detail" heading="{{ $role->name }}" subheading="Role access profile">
    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <dl class="grid gap-5 sm:grid-cols-2">
            <div><dt class="text-sm font-medium text-slate-500">Key</dt><dd class="mt-1 font-mono text-sm">{{ $role->key }}</dd></div>
            <div><dt class="text-sm font-medium text-slate-500">Assigned users</dt><dd class="mt-1">{{ $role->users_count }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-sm font-medium text-slate-500">Description</dt><dd class="mt-1 text-slate-700">{{ $role->description ?: 'No description provided.' }}</dd></div>
        </dl>
        <div class="mt-6">
            <h2 class="text-sm font-semibold text-slate-900">Permissions</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($role->permissions as $permission)
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $permission->key }}</span>
                @endforeach
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <a href="{{ route('admin.roles.edit', $role) }}" class="inline-flex min-h-11 items-center rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800">Edit</a>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back</a>
        </div>
    </section>
</x-layouts.admin>
