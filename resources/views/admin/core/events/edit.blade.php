<x-layouts.admin title="Event Settings" heading="Event Settings" eyebrow="Events">
    <x-ui.page-header
        eyebrow="Settings"
        title="{{ $event->title }}"
        description="Configure the organiser, site URL, dates, location, status, and duplicate-email registration setting."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.show', $event) }}" class="ds-button-secondary">Event Details</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'settings'])

    <form method="POST" action="{{ route('core.events.update', $event) }}">
        @csrf
        @method('PUT')
        @include('admin.core.events._form', ['event' => $event])
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('core.events.show', $event) }}" class="ds-button-secondary">Cancel</a>
            <button class="ds-button-primary" type="submit">Save Settings</button>
        </div>
    </form>
</x-layouts.admin>
