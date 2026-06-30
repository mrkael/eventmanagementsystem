<x-layouts.admin title="Users" heading="User Management" subheading="Provision accounts and assign access">
    <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-5 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" class="grid flex-1 gap-3 md:grid-cols-[1fr_180px_220px_auto]">
                <input name="search" value="{{ request('search') }}" placeholder="Search name or email" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                <select name="status" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ ucfirst($status->value) }}</option>
                    @endforeach
                </select>
                <select name="department_id" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                    <option value="">All departments</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-outline-primary btn-md">Filter</button>
            </form>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-md">New user</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">User</th><th class="px-5 py-3">Department</th><th class="px-5 py-3">Roles</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-5 py-4"><p class="font-semibold text-slate-900">{{ $user->name }}</p><p class="mt-1 text-slate-500">{{ $user->email }}</p></td>
                            <td class="px-5 py-4 text-slate-600">{{ $user->department?->name ?? '-' }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ ucfirst($user->status->value) }}</span></td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                                        @csrf @method('DELETE')
                                        <button @disabled($user->is(auth()->user())) class="btn btn-outline-danger btn-sm disabled:cursor-not-allowed disabled:opacity-40">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-5">{{ $users->links() }}</div>
    </section>
</x-layouts.admin>
