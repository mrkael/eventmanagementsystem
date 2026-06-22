<x-layouts.admin title="Roles" heading="Role Management" subheading="Assign grouped permissions to user roles">
    <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-5 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" class="flex flex-1 gap-3">
                <input name="search" value="{{ request('search') }}" placeholder="Search role" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                <button class="min-h-11 rounded-lg border border-slate-300 px-4 text-sm font-semibold hover:bg-slate-50">Filter</button>
            </form>
            <a href="{{ route('admin.roles.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800">New role</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Role</th><th class="px-5 py-3">Key</th><th class="px-5 py-3">Users</th><th class="px-5 py-3">Permissions</th><th class="px-5 py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($roles as $role)
                        <tr>
                            <td class="px-5 py-4"><a href="{{ route('admin.roles.show', $role) }}" class="font-semibold text-slate-900 hover:text-emerald-700">{{ $role->name }}</a><p class="mt-1 max-w-xl text-slate-500">{{ $role->description }}</p></td>
                            <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $role->key }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $role->users_count }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $role->permissions_count }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="rounded-lg border border-slate-300 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50">Edit</a>
                                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg border border-red-200 px-3 py-2 font-semibold text-red-700 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No roles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-5">{{ $roles->links() }}</div>
    </section>
</x-layouts.admin>
