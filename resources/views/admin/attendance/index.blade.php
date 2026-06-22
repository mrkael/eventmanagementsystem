<x-layouts.admin title="Attendance" heading="Attendance" subheading="{{ $event->title }}">
    @if(session('attendance_token'))
        <div class="mb-5 rounded-lg border border-emerald-200 bg-white p-5 shadow-sm" data-attendance-token="{{ session('attendance_token') }}">
            <div class="flex flex-col gap-5 md:flex-row md:items-center">
                <canvas data-qr-canvas class="size-48 rounded-lg border border-slate-200 bg-white p-2"></canvas>
                <div>
                    <p class="text-sm font-semibold text-emerald-700">QR generated</p>
                    <h2 class="mt-1 text-lg font-semibold">Participant ticket code</h2>
                    <p class="mt-2 break-all rounded-lg bg-slate-100 px-3 py-2 font-mono text-sm">{{ session('attendance_token') }}</p>
                    <p class="mt-2 text-sm text-slate-500">Print or display this QR for scanner check-in.</p>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.events.show', $event) }}" class="min-h-11 rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold hover:bg-slate-50">Event detail</a>
            <a href="{{ route('admin.events.attendance.scanner', $event) }}" class="min-h-11 rounded-lg bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-800">Open scanner</a>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.events.attendance.export', [$event, 'excel']) }}" class="min-h-11 rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold hover:bg-slate-50">Export Excel</a>
            <a href="{{ route('admin.events.attendance.export', [$event, 'pdf']) }}" class="min-h-11 rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold hover:bg-slate-50">Export PDF</a>
        </div>
    </div>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        @foreach([
            'Total Registered' => $metrics['total_registered'],
            'Checked In' => $metrics['total_checked_in'],
            'Checked Out' => $metrics['total_checked_out'],
            'Attendance Rate' => $metrics['attendance_rate'].'%',
            'No Show Rate' => $metrics['no_show_rate'].'%',
        ] as $label => $value)
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-bold">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_360px]">
        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold">Participants</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr><th class="px-4 py-3">Participant</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">In</th><th class="px-4 py-3">Out</th><th class="px-4 py-3">Actions</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($registrations as $registration)
                            <tr>
                                <td class="px-4 py-3"><p class="font-semibold">{{ $registration->name }}</p><p class="text-slate-500">{{ $registration->email }}</p></td>
                                <td class="px-4 py-3">{{ $registration->status->label() }}</td>
                                <td class="px-4 py-3">{{ $registration->checked_in_at?->format('d M H:i') ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $registration->checked_out_at?->format('d M H:i') ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <form method="POST" action="{{ route('admin.events.attendance.qr', [$event, $registration]) }}">@csrf<button class="rounded-lg border border-slate-300 px-3 py-2 font-semibold hover:bg-slate-50">QR</button></form>
                                        <form method="POST" action="{{ route('admin.events.attendance.override', [$event, $registration]) }}" class="flex flex-wrap gap-2">
                                            @csrf
                                            <input type="hidden" name="action" value="check_in">
                                            <input type="hidden" name="reason" value="Manual desk verification">
                                            <button class="rounded-lg border border-emerald-200 px-3 py-2 font-semibold text-emerald-700 hover:bg-emerald-50">Manual in</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.events.attendance.override', [$event, $registration]) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="no_show">
                                            <input type="hidden" name="reason" value="Marked from attendance dashboard">
                                            <button class="rounded-lg border border-amber-200 px-3 py-2 font-semibold text-amber-700 hover:bg-amber-50">No show</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No participants registered.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 p-4">{{ $registrations->links() }}</div>
        </section>

        <aside class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Scan history</h2>
            <div class="mt-4 space-y-3">
                @forelse($logs as $log)
                    <div class="rounded-lg border border-slate-200 p-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-semibold">{{ str_replace('_', ' ', ucfirst($log->action)) }}</p>
                            <span class="{{ $log->result === 'success' ? 'text-emerald-700' : 'text-red-700' }}">{{ ucfirst($log->result) }}</span>
                        </div>
                        <p class="mt-1 text-slate-600">{{ $log->registration?->name ?? 'Unknown QR' }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $log->created_at->format('d M Y H:i') }} by {{ $log->scanner?->name ?? 'System' }}</p>
                        @if($log->reason || $log->notes)<p class="mt-2 text-xs text-slate-500">{{ $log->reason ?: $log->notes }}</p>@endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No scan history yet.</p>
                @endforelse
            </div>
        </aside>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const wrapper = document.querySelector('[data-attendance-token]');
            if (wrapper && window.QRCode) {
                window.QRCode.toCanvas(wrapper.querySelector('[data-qr-canvas]'), wrapper.dataset.attendanceToken, { width: 192, margin: 2 });
            }
        });
    </script>
</x-layouts.admin>
