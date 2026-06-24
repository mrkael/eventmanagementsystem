<x-layouts.admin title="Create Event" heading="Create Event" eyebrow="Events">
    <x-ui.page-header
        eyebrow="New event"
        title="Create event"
        description="Start with the required event settings. Tickets can be created from the event detail area after saving."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.index') }}" class="ds-button-secondary">Back to Events</a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('core.events.store') }}">
        @csrf
        @include('admin.core.events._form')
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('core.events.index') }}" class="ds-button-secondary">Cancel</a>
            <button class="ds-button-primary" type="submit">Save Event</button>
        </div>
    </form>
</x-layouts.admin>
