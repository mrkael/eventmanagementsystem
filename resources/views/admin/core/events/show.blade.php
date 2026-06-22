<x-layouts.admin title="{{ $event->title }}" heading="{{ $event->title }}" subheading="Core event workspace">
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Tickets</p><p class="mt-2 text-3xl font-bold">{{ $event->tickets_count }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Registrations</p><p class="mt-2 text-3xl font-bold">{{ $event->core_registrations_count }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Sessions</p><p class="mt-2 text-3xl font-bold">{{ $event->sessions_count }}</p></div>
    </div>
    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <a class="rounded-lg border border-slate-200 bg-white p-4 font-semibold shadow-sm" href="{{ route('core.events.edit', $event) }}">Event setup</a>
        <a class="rounded-lg border border-slate-200 bg-white p-4 font-semibold shadow-sm" href="{{ route('core.events.microsite.edit', $event) }}">Microsite CMS</a>
        <a class="rounded-lg border border-slate-200 bg-white p-4 font-semibold shadow-sm" href="{{ route('core.events.forms.index', $event) }}">Registration forms</a>
        <a class="rounded-lg border border-slate-200 bg-white p-4 font-semibold shadow-sm" href="{{ route('core.events.tickets.index', $event) }}">Tickets & promos</a>
        <a class="rounded-lg border border-slate-200 bg-white p-4 font-semibold shadow-sm" href="{{ route('core.events.sessions.index', $event) }}">Sessions & check-in</a>
        <a class="rounded-lg border border-slate-200 bg-white p-4 font-semibold shadow-sm" href="{{ route('core.attendees.index', ['event_id' => $event->id]) }}">Attendees</a>
        <a class="rounded-lg border border-slate-200 bg-white p-4 font-semibold shadow-sm" href="{{ route('core.events.reports.index', $event) }}">Event report</a>
        @if($event->custom_url)
            <a class="rounded-lg border border-slate-200 bg-white p-4 font-semibold shadow-sm" target="_blank" href="{{ route('core.public.events.show', $event) }}">Public microsite</a>
        @else
            <a class="rounded-lg border border-slate-200 bg-white p-4 font-semibold text-slate-500 shadow-sm" href="{{ route('core.events.edit', $event) }}">Set public URL</a>
        @endif
    </div>
</x-layouts.admin>
