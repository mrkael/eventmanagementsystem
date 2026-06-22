<x-layouts.admin title="Permissions" heading="Permission Management" subheading="Control fine-grained system capabilities">
    <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-5 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" class="flex flex-1 flex-col gap-3 sm:flex-row">
                <input name="search" value="{{ request('search') }}" placeholder="Search permission" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                <select name="group" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                    <option value="">All groups</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group }}" @selected(request('group') === $group)>{{ $group }}</option>
                    @endforeach
                </select>
                <button class="min-h-11 rounded-lg border border-slate-300 px-4 text-sm font-semibold hover:bg-slate-50">Filter</button>
            </form>
            <a href="{{ route('admin.permissions.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800">New permission</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Permission</th><th class="px-5 py-3">Key</th><th class="px-5 py-3">Group</th><th class="px-5 py-3">Roles</th><th class="px-5 py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($permissions as $permission)
                        <tr>
                            <td class="px-5 py-4"><p class="font-semibold text-slate-900">{{ $permission->name }}</p><p class="mt-1 max-w-xl text-slate-500">{{ $permission->description }}</p></td>
                            <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $permission->key }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $permission->group }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $permission->roles_count }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.permissions.edit', $permission) }}" class="rounded-lg border border-slate-300 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50">Edit</a>
                                    <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" onsubmit="return confirm('Delete this permission?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg border border-red-200 px-3 py-2 font-semibold text-red-700 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No permissions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-5">{{ $permissions->links() }}</div>
    </section>
</x-layouts.admin>
