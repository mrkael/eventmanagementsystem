<x-layouts.admin title="Dashboard" heading="Admin Dashboard" subheading="Core access control overview">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $label => $value)
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium capitalize text-slate-500">{{ str_replace('_', ' ', $label) }}</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ number_format($value) }}</p>
            </div>
        @endforeach
    </div>

    <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-base font-semibold">Recent Audit Activity</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Action</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentAuditLogs as $log)
                        <tr>
                            <td class="px-5 py-4 font-medium text-slate-900">{{ $log->action }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $log->description }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $log->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">No audit activity yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.admin>
