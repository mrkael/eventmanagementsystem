@php
    $isEditingSession = filled($session);
    $selectedTickets = collect(old('ticket_ids', $session?->tickets?->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all();
@endphp

<x-layouts.admin title="Manage Sessions" heading="Manage Sessions" eyebrow="Event Details">
    <x-ui.page-header
        eyebrow="Agenda"
        title="{{ $agenda->title }}"
        description="Add sessions and assign the ticket types that will be eligible for future session check-in."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.agendas.index', $event) }}" class="ds-button-secondary">Back to Agendas</a>
            <a href="{{ route('core.events.agendas.edit', [$event, $agenda]) }}" class="ds-button-secondary">Edit Agenda</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'agenda'])

    <div class="grid gap-6 xl:grid-cols-[24rem_1fr]">
        <x-ui.card class="h-max xl:sticky xl:top-24">
            <div class="border-b border-slate-100 pb-5">
                <p class="text-xs font-black uppercase text-blue-600">{{ $isEditingSession ? 'Edit Session' : 'Add Session' }}</p>
                <h2 class="mt-1 text-xl font-black text-slate-950">{{ $isEditingSession ? $session->title : 'New session' }}</h2>
            </div>

            <form method="POST" action="{{ $isEditingSession ? route('core.events.agendas.sessions.update', [$event, $agenda, $session]) : route('core.events.agendas.sessions.store', [$event, $agenda]) }}" class="mt-5 space-y-4">
                @csrf
                @if($isEditingSession)
                    @method('PUT')
                @endif

                <label class="block">
                    <span class="ds-label">Session Title <span class="text-red-600">*</span></span>
                    <input name="title" value="{{ old('title', $session?->title) }}" required class="ds-input mt-2" placeholder="Opening keynote">
                    @error('title')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block">
                    <span class="ds-label">Description</span>
                    <textarea name="description" rows="4" class="ds-input mt-2 py-3" placeholder="Brief session description">{{ old('description', $session?->description) }}</textarea>
                    @error('description')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <label class="block">
                        <span class="ds-label">Session Type <span class="text-red-600">*</span></span>
                        <select name="session_type" required class="ds-input mt-2">
                            @foreach(['session' => 'Session', 'check_in' => 'Check In', 'keynote' => 'Keynote', 'workshop' => 'Workshop', 'panel' => 'Panel', 'break' => 'Break', 'networking' => 'Networking'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('session_type', $session?->session_type ?? 'session') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('session_type')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="ds-label">Capacity</span>
                        <input type="number" min="1" name="capacity" value="{{ old('capacity', $session?->capacity) }}" class="ds-input mt-2" placeholder="Optional">
                        @error('capacity')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                    </label>
                </div>

                <label class="block">
                    <span class="ds-label">Venue Name</span>
                    <input name="venue_name" value="{{ old('venue_name', $session?->venue_name ?? $session?->location) }}" class="ds-input mt-2" placeholder="Main Hall">
                    @error('venue_name')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <label class="block">
                        <span class="ds-label">Start Time <span class="text-red-600">*</span></span>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $session?->starts_at?->format('Y-m-d\\TH:i')) }}" required class="ds-input mt-2">
                        @error('starts_at')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="ds-label">End Time <span class="text-red-600">*</span></span>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $session?->ends_at?->format('Y-m-d\\TH:i')) }}" required class="ds-input mt-2">
                        @error('ends_at')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                    </label>
                </div>

                <label class="form-ticket-select block">
                    <span class="ds-label">Assigned Ticket <span class="text-red-600">*</span></span>
                    <select name="ticket_ids[]" multiple required data-agenda-ticket-select class="mt-2">
                        @foreach($tickets as $ticket)
                            <option value="{{ $ticket->id }}" @selected(in_array((string) $ticket->id, $selectedTickets, true))>{{ $ticket->name }}</option>
                        @endforeach
                    </select>
                    @error('ticket_ids')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                    @error('ticket_ids.*')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                    <span class="mt-2 block text-xs font-semibold text-slate-500">Search and select one or more tickets for this session.</span>
                </label>

                <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row">
                    @if($isEditingSession)
                        <a href="{{ route('core.events.agendas.show', [$event, $agenda]) }}" class="ds-button-secondary flex-1 justify-center">Cancel</a>
                    @endif
                    <button class="ds-button-primary flex-1 justify-center">{{ $isEditingSession ? 'Save Session' : 'Add Session' }}</button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card padding="p-0" class="overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-black text-slate-950">Session List</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Session Title</th>
                            <th class="px-5 py-4">Session Type</th>
                            <th class="px-5 py-4">Venue Name</th>
                            <th class="px-5 py-4">Capacity</th>
                            <th class="px-5 py-4">Start Time</th>
                            <th class="px-5 py-4">End Time</th>
                            <th class="px-5 py-4">Assigned Tickets</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($agenda->sessions as $agendaSession)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <p class="font-black text-slate-950">{{ $agendaSession->title }}</p>
                                    @if($agendaSession->description)
                                        <p class="mt-1 max-w-xs text-xs leading-5 text-slate-500">{{ $agendaSession->description }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4"><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">{{ str($agendaSession->session_type)->headline() }}</span></td>
                                <td class="px-5 py-4 text-slate-600">{{ $agendaSession->venue_name ?: $agendaSession->location ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $agendaSession->capacity ? number_format($agendaSession->capacity) : '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $agendaSession->starts_at?->format('d M Y, H:i') }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $agendaSession->ends_at?->format('d M Y, H:i') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex max-w-sm flex-wrap gap-2">
                                        @forelse($agendaSession->tickets as $ticket)
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $ticket->name }}</span>
                                        @empty
                                            <span class="text-xs font-semibold text-red-600">No ticket assigned</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('core.events.agendas.sessions.edit', [$event, $agenda, $agendaSession]) }}" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-white">Edit</a>
                                        <form method="POST" action="{{ route('core.events.agendas.sessions.destroy', [$event, $agenda, $agendaSession]) }}" onsubmit="return confirm('Delete this session?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-red-200 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-10">
                                    <x-ui.empty-state icon="calendar" title="No sessions yet" description="Add the first session and assign one or more eligible ticket types." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    <script>
        (() => {
            const bootAgendaTicketSelect = () => {
                if (!window.TomSelect) {
                    window.setTimeout(bootAgendaTicketSelect, 50);
                    return;
                }

                document.querySelectorAll('[data-agenda-ticket-select]').forEach((select) => {
                    if (select.tomselect) return;

                    new window.TomSelect(select, {
                        plugins: ['remove_button'],
                        maxItems: null,
                        persist: false,
                        create: false,
                        hideSelected: true,
                        closeAfterSelect: false,
                        placeholder: 'Search and select ticket',
                    });
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootAgendaTicketSelect);
            } else {
                bootAgendaTicketSelect();
            }
        })();
    </script>
</x-layouts.admin>
