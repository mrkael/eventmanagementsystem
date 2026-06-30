<x-layouts.admin title="Audit Logs" heading="Audit Logs" subheading="Review security and operational activity">
    <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5">
            <form method="GET" class="grid gap-3 lg:grid-cols-[1fr_220px_170px_170px_auto]">
                <input name="search" value="{{ request('search') }}" placeholder="Search action or description" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                <select name="user_id" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                    <option value="">All users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20" aria-label="Date from">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20" aria-label="Date to">
                <button class="btn btn-outline-primary btn-md">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Action</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Source</th>
                        <th class="px-5 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($auditLogs as $log)
                        <tr>
                            <td class="px-5 py-4 font-mono text-xs text-slate-800">{{ $log->action }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $log->description }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $log->ip_address ?: '-' }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $log->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No audit logs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-5">{{ $auditLogs->links() }}</div>
    </section>
</x-layouts.admin>
