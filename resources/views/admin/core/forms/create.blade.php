<x-layouts.admin title="Create Form" heading="Create Form" eyebrow="Forms">
    <x-ui.page-header
        eyebrow="New form"
        title="{{ $event->title }}"
        description="Build a registration form and assign it to the ticket types that should use it."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.forms.index', $event) }}" class="ds-button-secondary">Back to Forms</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'forms'])

    <form method="POST" action="{{ route('core.events.forms.store', $event) }}" data-form-builder>
        @csrf
        @include('admin.core.forms._builder')
    </form>
</x-layouts.admin>
