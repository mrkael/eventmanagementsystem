<x-layouts.admin title="Attendees" heading="Attendees" eyebrow="Event Details">
    <x-ui.page-header
        eyebrow="Attendees"
        title="{{ $event->title }}"
        description="Manage registered participants for this event, resend confirmation emails, and export attendee data."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.show', $event) }}" class="ds-button-secondary">Event Details</a>
            <a href="{{ route('core.events.attendees.export', array_merge(['event' => $event], request()->query())) }}" class="ds-button-secondary"><x-ui.icon name="upload" class="size-4" /> Export</a>
            <a href="{{ route('core.events.attendees.create', $event) }}" class="ds-button-primary"><x-ui.icon name="plus" class="size-4" /> Add Attendee</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'attendees'])

    <x-ui.card class="mb-6">
        <form method="GET" class="grid gap-3 lg:grid-cols-[1.3fr_1fr_1fr_1fr_1fr_auto]">
            <label>
                <span class="ds-label">Search</span>
                <input name="search" value="{{ request('search') }}" class="ds-input mt-2" placeholder="Name, email, or reference">
            </label>
            <label>
                <span class="ds-label">Ticket</span>
                <select name="ticket_id" class="ds-input mt-2">
                    <option value="">All tickets</option>
                    @foreach($tickets as $ticket)
                        <option value="{{ $ticket->id }}" @selected((string) request('ticket_id') === (string) $ticket->id)>{{ $ticket->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="ds-label">Status</span>
                <select name="status" class="ds-input mt-2">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->headline() }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="ds-label">From</span>
                <input type="date" name="registered_from" value="{{ request('registered_from') }}" class="ds-input mt-2">
            </label>
            <label>
                <span class="ds-label">Until</span>
                <input type="date" name="registered_until" value="{{ request('registered_until') }}" class="ds-input mt-2">
            </label>
            <div class="flex items-end gap-2">
                <button class="ds-button-primary min-h-11">Apply</button>
                <a href="{{ route('core.events.attendees.index', $event) }}" class="ds-button-secondary min-h-11">Reset</a>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card padding="p-0" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Reference</th>
                        <th class="px-5 py-4">Participant</th>
                        <th class="px-5 py-4">Ticket</th>
                        <th class="px-5 py-4">Registered</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Email</th>
                        <th class="px-5 py-4">QR</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($registrations as $registration)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-5 py-4 font-black text-slate-950">{{ $registration->reference_number }}</td>
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-950">{{ $registration->full_name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $registration->email }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $registration->ticket?->name ?? '-' }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $registration->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $registration->status === 'confirmed' ? 'bg-emerald-50 text-emerald-700' : ($registration->status === 'cancelled' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600') }}">{{ str($registration->status)->headline() }}</span></td>
                            <td class="px-5 py-4">{{ $registration->confirmation_email_sent_at ? 'Sent' : 'Not sent' }}</td>
                            <td class="px-5 py-4">{{ $registration->qr_token ? 'Generated' : 'Missing' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('core.events.attendees.show', [$event, $registration]) }}" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-white">View</a>
                                    <a href="{{ route('core.events.attendees.edit', [$event, $registration]) }}" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-white">Edit</a>
                                    <form method="POST" action="{{ route('core.events.attendees.resend', [$event, $registration]) }}">
                                        @csrf
                                        <button class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-white">Resend</button>
                                    </form>
                                    <form method="POST" action="{{ route('core.events.attendees.cancel', [$event, $registration]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button @disabled($registration->status === 'cancelled') class="rounded-full border border-red-200 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50 disabled:opacity-40">Cancel</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10">
                                <x-ui.empty-state icon="users" title="No attendees yet" description="Add the first attendee manually or wait for public registrations to appear here." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-6">{{ $registrations->links() }}</div>
</x-layouts.admin>
