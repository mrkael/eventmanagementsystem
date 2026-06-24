<x-layouts.admin title="Create Ticket" heading="Create Ticket" eyebrow="Tickets">
    <x-ui.page-header
        eyebrow="New ticket"
        title="{{ $event->title }}"
        description="Create a ticket type for this event. Pricing and promo code workflows are intentionally deferred."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.tickets.index', $event) }}" class="ds-button-secondary">Back to Tickets</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'tickets'])

    <form method="POST" action="{{ route('core.events.tickets.store', $event) }}">
        @csrf
        @include('admin.core.tickets._form')
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('core.events.tickets.index', $event) }}" class="ds-button-secondary">Cancel</a>
            <button type="submit" class="ds-button-primary">Save Ticket</button>
        </div>
    </form>
</x-layouts.admin>
