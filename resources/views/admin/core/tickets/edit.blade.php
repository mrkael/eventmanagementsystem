<x-layouts.admin title="Edit Ticket" heading="Edit Ticket" eyebrow="Tickets">
    <x-ui.page-header
        eyebrow="Edit ticket"
        title="{{ $ticket->name }}"
        description="Update ticket quantities, active date range, visibility, status, and registration form relationship."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.tickets.index', $event) }}" class="ds-button-secondary">Back to Tickets</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'tickets'])

    <form method="POST" action="{{ route('core.events.tickets.update', [$event, $ticket]) }}">
        @csrf
        @method('PUT')
        @include('admin.core.tickets._form', ['ticket' => $ticket])
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('core.events.tickets.index', $event) }}" class="ds-button-secondary">Cancel</a>
            <button type="submit" class="ds-button-primary">Update Ticket</button>
        </div>
    </form>
</x-layouts.admin>
