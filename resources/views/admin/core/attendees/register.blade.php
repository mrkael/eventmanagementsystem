@php
    $isEdit = filled($registration);
    $form = $ticket->form;
    $fields = $form?->fields ?? collect();
    $hasFullName = $fields->contains('key', 'full_name');
    $hasEmail = $fields->contains('key', 'email');
@endphp

<x-layouts.admin title="{{ $isEdit ? 'Edit Attendee' : 'Register Attendee' }}" heading="{{ $isEdit ? 'Edit Attendee' : 'Register Attendee' }}" eyebrow="Event Details">
    <x-ui.page-header
        eyebrow="{{ $isEdit ? 'Edit attendee' : 'Step 2 of 2' }}"
        title="{{ $form?->title ?? 'Registration Form' }}"
        description="Ticket: {{ $ticket->name }}. Complete the assigned registration form to create the attendee record."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.attendees.index', $event) }}" class="ds-button-secondary">Back to Attendees</a>
            @unless($isEdit)
                <a href="{{ route('core.events.attendees.create', $event) }}" class="ds-button-secondary">Change Ticket</a>
            @endunless
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'attendees'])

    @if($errors->any())
        <div class="mb-5 rounded-[20px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-black">Please review the form.</p>
            <p class="mt-1 font-semibold">{{ $errors->first() }}</p>
        </div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('core.events.attendees.update', [$event, $registration]) : route('core.events.attendees.store', [$event, $ticket]) }}" enctype="multipart/form-data" class="mx-auto max-w-4xl">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <x-ui.card>
            <div class="border-b border-slate-100 pb-5">
                <p class="text-xs font-black uppercase text-blue-600">Participant Identity</p>
                <h2 class="mt-1 text-2xl font-black text-slate-950">{{ $isEdit ? $registration->reference_number : 'New attendee' }}</h2>
            </div>

            @if(! $hasFullName || ! $hasEmail)
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @unless($hasFullName)
                        <label class="block">
                            <span class="ds-label">Full Name <span class="text-red-600">*</span></span>
                            <input name="full_name" value="{{ old('full_name', $registration?->full_name) }}" required class="ds-input mt-2" placeholder="Participant full name">
                            @error('full_name')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                        </label>
                    @endunless
                    @unless($hasEmail)
                        <label class="block">
                            <span class="ds-label">Email <span class="text-red-600">*</span></span>
                            <input type="email" name="email" value="{{ old('email', $registration?->email) }}" required class="ds-input mt-2" placeholder="participant@example.com">
                            @error('email')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                        </label>
                    @endunless
                </div>
            @else
                <p class="mt-5 rounded-2xl bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-500">Name and email are captured by the assigned registration form.</p>
            @endif
        </x-ui.card>

        <x-ui.card class="mt-5">
            <div class="border-b border-slate-100 pb-5">
                <p class="text-xs font-black uppercase text-blue-600">Registration Form</p>
                <h2 class="mt-1 text-2xl font-black text-slate-950">{{ $form?->title }}</h2>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                @forelse($fields as $field)
                    @include('admin.core.attendees._field', ['field' => $field, 'answers' => $answers])
                @empty
                    <div class="md:col-span-2">
                        <x-ui.empty-state icon="editor" title="No form fields" description="This ticket has a form assigned, but the form does not contain fields yet." />
                    </div>
                @endforelse
            </div>
        </x-ui.card>

        <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('core.events.attendees.index', $event) }}" class="ds-button-secondary justify-center">Cancel</a>
            <button class="ds-button-primary justify-center">{{ $isEdit ? 'Save Changes' : 'Save / Register' }}</button>
        </div>
    </form>
</x-layouts.admin>
