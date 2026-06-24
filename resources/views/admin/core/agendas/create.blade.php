@php($isEdit = filled($agenda))

<x-layouts.admin title="{{ $isEdit ? 'Edit Agenda' : 'Add Agenda' }}" heading="{{ $isEdit ? 'Edit Agenda' : 'Add Agenda' }}" eyebrow="Event Details">
    <x-ui.page-header
        eyebrow="Agenda"
        title="{{ $isEdit ? 'Edit agenda' : 'Create agenda' }}"
        description="Add a simple agenda title, then continue to session management."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.agendas.index', $event) }}" class="ds-button-secondary">Back to Agenda</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'agenda'])

    <form method="POST" action="{{ $isEdit ? route('core.events.agendas.update', [$event, $agenda]) : route('core.events.agendas.store', $event) }}" class="mx-auto max-w-3xl">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <x-ui.card>
            <label class="block">
                <span class="ds-label">Agenda Title <span class="text-red-600">*</span></span>
                <input name="title" value="{{ old('title', $agenda?->title) }}" required class="ds-input mt-2" placeholder="Main Conference Agenda">
                @error('title')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
            </label>
        </x-ui.card>

        <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('core.events.agendas.index', $event) }}" class="ds-button-secondary justify-center">Cancel</a>
            <button class="ds-button-primary justify-center">{{ $isEdit ? 'Save Changes' : 'Save & Continue' }}</button>
        </div>
    </form>
</x-layouts.admin>
