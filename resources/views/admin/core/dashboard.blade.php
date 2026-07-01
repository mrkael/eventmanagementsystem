@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

    $statusBadge = [
        'published' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'submitted' => 'bg-blue-50 text-blue-700 border-blue-200',
        'draft'     => 'bg-slate-100 text-slate-600 border-slate-200',
    ];
@endphp

<x-layouts.admin title="Dashboard" heading="Dashboard" eyebrow="Workspace">

    {{-- Page header --}}
    <x-ui.page-header
        eyebrow="Dashboard"
        title="{{ $greeting }}, {{ auth()->user()->name }}"
        description="Overview of your event performance and recent activity."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.create') }}" class="ds-button-primary">
                <x-ui.icon name="plus" class="size-4" /> New Event
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- ── Zone 1: Event selector + KPI cards ──────────────────────────── --}}
    <div
        data-dashboard-root
        data-stats-url="{{ route('dashboard.stats') }}"
        data-initial-event-id="{{ $selectedEvent?->id }}"
    >
        {{-- Event selector --}}
        <div class="flex flex-wrap items-end gap-4">
            <div class="min-w-0 flex-1 max-w-sm">
                <label class="block">
                    <span class="ds-label">Viewing event</span>
                    <select data-event-select class="mt-2 w-full">
                        <option value="">— Select an event —</option>
                        @foreach ($events as $evt)
                            <option value="{{ $evt->id }}" @selected($evt->id === $selectedEvent?->id)>
                                {{ $evt->title }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>
            @if ($selectedEvent)
                <p data-event-meta class="pb-1 text-sm font-semibold text-slate-500">
                    {{ $selectedEvent->starts_at?->format('d M Y') ?? 'Date TBC' }}
                    @if ($selectedEvent->ends_at && ! $selectedEvent->starts_at?->isSameDay($selectedEvent->ends_at))
                        – {{ $selectedEvent->ends_at->format('d M Y') }}
                    @endif
                    &nbsp;·&nbsp;
                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-bold {{ $statusBadge[$selectedEvent->status_key->value ?? $selectedEvent->status_key] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                        {{ ucfirst($selectedEvent->status_key->value ?? $selectedEvent->status_key) }}
                    </span>
                </p>
            @endif
        </div>

        {{-- KPI cards --}}
        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.card>
                <div class="flex items-start justify-between gap-2">
                    <p class="text-xs font-black uppercase tracking-wide text-slate-500">Total Registrations</p>
                    <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-slate-100">
                        <x-ui.icon name="users" class="size-4 text-slate-600" />
                    </span>
                </div>
                <p data-stat-total class="mt-4 text-4xl font-black text-slate-950">
                    {{ number_format($stats['total_registrations']) }}
                </p>
                <p class="mt-1.5 text-xs font-semibold text-slate-400">Confirmed &amp; registered</p>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-start justify-between gap-2">
                    <p class="text-xs font-black uppercase tracking-wide text-slate-500">This Week</p>
                    <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-blue-50">
                        <x-ui.icon name="calendar" class="size-4 text-blue-600" />
                    </span>
                </div>
                <p data-stat-week class="mt-4 text-4xl font-black text-blue-700">
                    {{ number_format($stats['this_week']) }}
                </p>
                <p class="mt-1.5 text-xs font-semibold text-slate-400">New since {{ now()->startOfWeek()->format('D, d M') }}</p>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-start justify-between gap-2">
                    <p class="text-xs font-black uppercase tracking-wide text-slate-500">Total Check-Ins</p>
                    <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-emerald-50">
                        <x-ui.icon name="check" class="size-4 text-emerald-600" />
                    </span>
                </div>
                <p data-stat-checkins class="mt-4 text-4xl font-black text-emerald-700">
                    {{ number_format($stats['total_check_ins']) }}
                </p>
                <p class="mt-1.5 text-xs font-semibold text-slate-400">Across all sessions</p>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-start justify-between gap-2">
                    <p class="text-xs font-black uppercase tracking-wide text-slate-500">Today's Check-Ins</p>
                    <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-amber-50">
                        <x-ui.icon name="spark" class="size-4 text-amber-600" />
                    </span>
                </div>
                <p data-stat-today class="mt-4 text-4xl font-black text-amber-700">
                    {{ number_format($stats['today_check_ins']) }}
                </p>
                <p class="mt-1.5 text-xs font-semibold text-slate-400">{{ today()->format('d M Y') }}</p>
            </x-ui.card>
        </div>

        {{-- Zone 2: Live now banner (shown when selected event is happening today) --}}
        <div data-live-banner class="{{ $isLiveToday ? '' : 'hidden' }} mt-5 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">
            <div class="flex items-center gap-3">
                <span class="relative flex size-3">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex size-3 rounded-full bg-emerald-500"></span>
                </span>
                <div>
                    <p class="text-sm font-black text-emerald-900">Event is live today</p>
                    <p class="mt-0.5 text-xs font-semibold text-emerald-700">Check-in is available. Open the scanner to start scanning participants.</p>
                </div>
            </div>
            <a data-checkin-link href="{{ $selectedEvent ? route('core.events.check-in.index', $selectedEvent) : '#' }}" class="ds-button-primary shrink-0 !bg-emerald-700 hover:!bg-emerald-800">
                Open Scanner
            </a>
        </div>
    </div>

    {{-- ── Zone 3 + 4: Events list + Recent registrations ─────────────── --}}
    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_380px]">

        {{-- Zone 3: Events list --}}
        <x-ui.card padding="p-0" class="overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-black text-slate-950">Your Events</h2>
                <a href="{{ route('core.events.index') }}" class="text-xs font-bold text-blue-600 hover:underline">View all</a>
            </div>

            @if ($events->isEmpty())
                <div class="px-5 py-10">
                    <x-ui.empty-state icon="calendar" title="No events yet" description="Create your first event to get started." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Event</th>
                                <th class="px-5 py-3">Date</th>
                                <th class="px-5 py-3">Registrations</th>
                                <th class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($events as $evt)
                                @php
                                    $count = $registrationCounts->get($evt->id, 0);
                                    $capacity = $evt->capacity ?? 0;
                                    $pct = ($capacity > 0) ? min(100, round(($count / $capacity) * 100)) : null;
                                    $statusKey = $evt->status_key->value ?? $evt->status_key;
                                    $isLive = $evt->starts_at && $evt->starts_at->startOfDay()->lte(now())
                                        && (! $evt->ends_at || $evt->ends_at->endOfDay()->gte(now()));
                                @endphp
                                <tr class="transition hover:bg-slate-50 {{ $evt->id === $selectedEvent?->id ? 'bg-blue-50/40' : '' }}">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2.5">
                                            @if ($isLive)
                                                <span class="relative flex size-2">
                                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                                    <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                                                </span>
                                            @endif
                                            <div>
                                                <p class="font-bold text-slate-950">{{ $evt->title }}</p>
                                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $statusBadge[$statusKey] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                                    {{ ucfirst($statusKey) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">
                                        {{ $evt->starts_at?->format('d M Y') ?? '—' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2.5">
                                            <span class="min-w-[2rem] font-bold text-slate-950">{{ number_format($count) }}</span>
                                            @if ($pct !== null)
                                                <div class="h-1.5 w-20 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 60 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                         style="width: {{ $pct }}%"></div>
                                                </div>
                                                <span class="text-xs text-slate-400">{{ $pct }}%</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('core.events.show', $evt) }}" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50">
                                                View
                                            </a>
                                            @if ($statusKey === 'published')
                                                <a href="{{ route('core.events.check-in.index', $evt) }}" class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100">
                                                    Check-In
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>

        {{-- Zone 4: Recent registrations (scoped to selected event) --}}
        <x-ui.card padding="p-0" class="overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-black text-slate-950">Recent Registrations</h2>
                <p class="mt-0.5 text-xs font-semibold text-slate-400">Latest 10 for selected event</p>
            </div>

            <div data-recent-empty class="{{ $recentRegistrations->isEmpty() ? '' : 'hidden' }} px-5 py-10">
                <x-ui.empty-state icon="users" title="No registrations yet" description="Registrations for the selected event will appear here." />
            </div>

            <table class="min-w-full text-sm {{ $recentRegistrations->isEmpty() ? 'hidden' : '' }}" data-recent-table>
                <tbody data-recent-body class="divide-y divide-slate-100">
                    @foreach ($recentRegistrations as $reg)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-5 py-3.5">
                                <p class="font-bold text-slate-950">{{ $reg->full_name }}</p>
                                <p class="text-xs text-slate-400">{{ $reg->email }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <p class="text-xs font-semibold text-slate-600">{{ $reg->ticket?->name ?? '—' }}</p>
                                <p class="text-xs text-slate-400">{{ $reg->created_at->diffForHumans() }}</p>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-ui.card>

    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-dashboard-root]');
            if (!root) return;

            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const statsUrl = root.dataset.statsUrl;
            const eventSelect = root.querySelector('[data-event-select]');
            const liveBanner = root.querySelector('[data-live-banner]');
            const checkinLink = root.querySelector('[data-checkin-link]');
            const recentBody = document.querySelector('[data-recent-body]');
            const recentTable = document.querySelector('[data-recent-table]');
            const recentEmpty = document.querySelector('[data-recent-empty]');

            const statEls = {
                total:    root.querySelector('[data-stat-total]'),
                week:     root.querySelector('[data-stat-week]'),
                checkins: root.querySelector('[data-stat-checkins]'),
                today:    root.querySelector('[data-stat-today]'),
            };

            const fmt = (n) => Number(n ?? 0).toLocaleString();
            const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));

            const setLoading = () => {
                Object.values(statEls).forEach((el) => { if (el) el.textContent = '…'; });
            };

            const renderStats = (stats) => {
                if (statEls.total)    statEls.total.textContent    = fmt(stats.total_registrations);
                if (statEls.week)     statEls.week.textContent     = fmt(stats.this_week);
                if (statEls.checkins) statEls.checkins.textContent = fmt(stats.total_check_ins);
                if (statEls.today)    statEls.today.textContent    = fmt(stats.today_check_ins);
            };

            const renderLiveBanner = (isLive, checkInUrl) => {
                if (! liveBanner) return;
                liveBanner.classList.toggle('hidden', ! isLive);
                if (isLive && checkinLink && checkInUrl) {
                    checkinLink.href = checkInUrl;
                }
            };

            const renderRecentRegistrations = (rows) => {
                if (! recentBody) return;
                const hasRows = rows && rows.length > 0;
                recentTable?.classList.toggle('hidden', ! hasRows);
                recentEmpty?.classList.toggle('hidden', hasRows);
                if (! hasRows) return;
                recentBody.innerHTML = rows.map((r) => `
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-5 py-3.5">
                            <p class="font-bold text-slate-950">${esc(r.name)}</p>
                            <p class="text-xs text-slate-400">${esc(r.email)}</p>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <p class="text-xs font-semibold text-slate-600">${esc(r.ticket || '—')}</p>
                            <p class="text-xs text-slate-400">${esc(r.time_ago)}</p>
                        </td>
                    </tr>
                `).join('');
            };

            const fetchStats = async (eventId) => {
                if (! eventId) {
                    Object.values(statEls).forEach((el) => { if (el) el.textContent = '0'; });
                    renderLiveBanner(false, null);
                    renderRecentRegistrations([]);
                    return;
                }

                setLoading();

                try {
                    const res = await fetch(`${statsUrl}?event_id=${eventId}`, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    });
                    if (! res.ok) return;
                    const data = await res.json();
                    renderStats(data.stats);
                    renderLiveBanner(data.is_live_today, data.check_in_url);
                    renderRecentRegistrations(data.recent_registrations);
                } catch {}
            };

            eventSelect?.addEventListener('change', (e) => fetchStats(e.target.value));

            // Boot TomSelect on the event selector if available
            const bootSelect = () => {
                if (! window.TomSelect) { window.setTimeout(bootSelect, 50); return; }
                if (! eventSelect?.tomselect) {
                    new window.TomSelect(eventSelect, {
                        maxItems: 1,
                        create: false,
                        placeholder: 'Search and select event',
                        onChange: (value) => fetchStats(value),
                    });
                }
            };
            bootSelect();
        })();
    </script>

</x-layouts.admin>
