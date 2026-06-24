<x-layouts.admin title="{{ $event->title }}" heading="{{ $event->title }}" eyebrow="Event Details">
    <x-ui.page-header
        eyebrow="Event Details"
        title="{{ $event->title }}"
        description="Open Settings to configure the event, or Tickets to create and manage ticket types for this event."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.index') }}" class="ds-button-secondary">Back to Events</a>
            <a href="{{ route('core.events.edit', $event) }}" class="ds-button-primary">Open Settings</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'settings'])

    @php($eventStatus = $event->status_key instanceof \BackedEnum ? $event->status_key->value : $event->status_key)

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card>
            <p class="text-sm font-bold text-slate-500">Organiser Profile</p>
            <p class="mt-3 text-xl font-semibold text-slate-950">{{ $event->organiserProfile?->name ?? 'Not assigned' }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $event->organiserProfile?->email }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-sm font-bold text-slate-500">Event URL</p>
            <p class="mt-3 break-all font-mono text-sm text-slate-950">{{ $event->custom_url ? url('/e/'.$event->custom_url) : url('/e/your-event-url') }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-sm font-bold text-slate-500">Tickets</p>
            <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($event->tickets_count) }}</p>
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6">
        <dl class="grid gap-5 md:grid-cols-2">
            <div><dt class="ds-label">Event Date</dt><dd class="mt-2 text-slate-700">{{ $event->starts_at?->format('d M Y, H:i') }} - {{ $event->ends_at?->format('d M Y, H:i') }}</dd></div>
            <div><dt class="ds-label">Status</dt><dd class="mt-2 text-slate-700">{{ ucfirst($eventStatus) }}</dd></div>
            <div><dt class="ds-label">Location</dt><dd class="mt-2 text-slate-700">{{ $event->location ?: '-' }}</dd></div>
            <div><dt class="ds-label">Multiple Email Registration</dt><dd class="mt-2 text-slate-700">{{ $event->allow_duplicate_email ? 'Allowed' : 'Not allowed' }}</dd></div>
            <div class="md:col-span-2"><dt class="ds-label">Description</dt><dd class="mt-2 whitespace-pre-line text-slate-700">{{ $event->description ?: '-' }}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.admin>
