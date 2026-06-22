<x-layouts.admin title="Admin Registration" heading="Admin Registration" subheading="{{ $event->title }}">
    <section class="mx-auto max-w-4xl rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @if(! $form)
            <p class="text-sm text-slate-600">Create a registration form before adding participants.</p>
            <a href="{{ route('admin.events.registrations.builder.edit', $event) }}" class="mt-4 inline-flex min-h-11 items-center rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white">Open builder</a>
        @else
            <form method="POST" action="{{ route('admin.events.registrations.store', $event) }}" enctype="multipart/form-data">
                @csrf
                @include('registrations._dynamic_form', ['form' => $form, 'submitLabel' => 'Register participant'])
            </form>
        @endif
    </section>
</x-layouts.admin>
