<x-layouts.admin title="Tickets" heading="Tickets" eyebrow="Event Details">
    <x-ui.page-header
        eyebrow="Tickets"
        title="{{ $event->title }}"
        description="Create and manage ticket types for this event. Hidden tickets are saved now and will be excluded from future public registration pages."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.show', $event) }}" class="ds-button-secondary">Event Details</a>
            <a href="{{ route('core.events.tickets.create', $event) }}" class="ds-button-primary"><x-ui.icon name="plus" class="size-4" /> Create Ticket</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'tickets'])

    <x-ui.card class="mb-6 border-blue-200 bg-blue-50/80">
        <p class="text-sm font-bold text-blue-900">Ticket assignment happens in Forms</p>
        <p class="mt-2 text-sm leading-6 text-blue-800">Create tickets here first. Then open the Forms tab and assign one or more available tickets to the registration form.</p>
    </x-ui.card>

    <x-ui.card padding="p-0" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Ticket Name</th>
                        <th class="px-5 py-4">Description</th>
                        <th class="px-5 py-4">Quantity</th>
                        <th class="px-5 py-4">Min Quantity</th>
                        <th class="px-5 py-4">Max Quantity</th>
                        <th class="px-5 py-4">Hidden Ticket</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Created Date</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tickets as $ticket)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-950">{{ $ticket->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $ticket->form?->title ?? 'No registration form assigned' }}</p>
                            </td>
                            <td class="max-w-sm px-5 py-4 text-slate-600">{{ $ticket->description ?: '-' }}</td>
                            <td class="px-5 py-4 font-semibold text-slate-700">{{ number_format($ticket->quantity) }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ number_format($ticket->min_quantity) }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ number_format($ticket->max_quantity) }}</td>
                            <td class="px-5 py-4">{{ $ticket->is_hidden ? 'Yes' : 'No' }}</td>
                            <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $ticket->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($ticket->status) }}</span></td>
                            <td class="px-5 py-4 text-slate-500">{{ $ticket->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('core.events.tickets.edit', [$event, $ticket]) }}" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-white">Edit</a>
                                    <form method="POST" action="{{ route('core.events.tickets.destroy', [$event, $ticket]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-full border border-red-200 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-10">
                                <x-ui.empty-state icon="ticket" title="No tickets yet" description="Create the first ticket for this event before future registration pages are opened." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-6">{{ $tickets->links() }}</div>
</x-layouts.admin>
