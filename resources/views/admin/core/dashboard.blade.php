<x-layouts.admin title="Dashboard" heading="Core Event Dashboard" subheading="Registrations, tickets, and attendance only">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Total Events</p><p class="mt-2 text-3xl font-bold">{{ number_format($totalEvents) }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Active Events</p><p class="mt-2 text-3xl font-bold">{{ number_format($activeEvents) }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Registrations</p><p class="mt-2 text-3xl font-bold">{{ number_format($totalRegistrations) }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Attendance Rate</p><p class="mt-2 text-3xl font-bold">{{ $attendancePercentage }}%</p></div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-semibold">Ticket Registrations</h2></div>
            <div class="divide-y divide-slate-100">
                @forelse($ticketCounts as $ticket)
                    <div class="flex items-center justify-between px-5 py-4 text-sm"><span>{{ $ticket->name }}</span><span class="font-semibold">{{ $ticket->registrations_count }}</span></div>
                @empty
                    <p class="px-5 py-6 text-sm text-slate-500">No tickets yet.</p>
                @endforelse
            </div>
        </section>
        <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-semibold">Session Check-Ins</h2></div>
            <div class="divide-y divide-slate-100">
                @forelse($sessionCounts as $session)
                    <div class="flex items-center justify-between px-5 py-4 text-sm"><span>{{ $session->title }}</span><span class="font-semibold">{{ $session->checked_in_count }}</span></div>
                @empty
                    <p class="px-5 py-6 text-sm text-slate-500">No sessions yet.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-semibold">Recent Registrations</h2></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500"><tr><th class="px-5 py-3">Reference</th><th class="px-5 py-3">Participant</th><th class="px-5 py-3">Event</th><th class="px-5 py-3">Ticket</th><th class="px-5 py-3">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentRegistrations as $registration)
                        <tr><td class="px-5 py-4 font-medium">{{ $registration->reference_number }}</td><td class="px-5 py-4">{{ $registration->full_name }}</td><td class="px-5 py-4">{{ $registration->event?->title }}</td><td class="px-5 py-4">{{ $registration->ticket?->name }}</td><td class="px-5 py-4">{{ ucfirst($registration->status) }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No registrations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.admin>
