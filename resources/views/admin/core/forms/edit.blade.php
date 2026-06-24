<x-layouts.admin title="Edit Form" heading="Edit Form" eyebrow="Forms">
    <x-ui.page-header
        eyebrow="Edit form"
        title="{{ $form->title }}"
        description="Update form fields, custom questions, ticket assignment, and form status."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.forms.preview', [$event, $form]) }}" class="ds-button-secondary">Preview Form</a>
            <a href="{{ route('core.events.forms.index', $event) }}" class="ds-button-secondary">Back to Forms</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'forms'])

    <form method="POST" action="{{ route('core.events.forms.update', [$event, $form]) }}" data-form-builder>
        @csrf
        @method('PUT')
        @include('admin.core.forms._builder')
    </form>
</x-layouts.admin>
