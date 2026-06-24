<x-layouts.admin title="Email Preview" heading="Email Preview" eyebrow="Event Details">
    <x-ui.page-header
        eyebrow="Confirmation email"
        title="{{ $event->title }}"
        description="Preview with sample participant, ticket, form, and QR data."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.email.edit', $event) }}" class="ds-button-secondary">Back to Email</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'email'])

    <div class="mx-auto max-w-3xl rounded-[28px] border border-slate-200 bg-slate-50 p-5 shadow-soft">
        <div class="rounded-[22px] border border-slate-200 bg-white p-6">
            <p class="text-xs font-black uppercase text-slate-400">Subject</p>
            <h2 class="mt-2 text-2xl font-black text-slate-950">{{ $preview['subject'] }}</h2>
            @if($preview['header'])
                <div class="mt-6 text-xl font-bold text-slate-950">{!! $preview['header'] !!}</div>
            @endif
            <div class="mt-5 text-sm leading-7 text-slate-700">{!! $preview['body'] !!}</div>
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-center">
                <p class="text-sm font-black uppercase text-emerald-700">QR Code</p>
                <img src="{{ $preview['qr'] }}" alt="Sample QR code" class="mx-auto mt-3 size-44">
            </div>
            @if($preview['footer'])
                <div class="mt-6 border-t border-slate-200 pt-4 text-sm leading-6 text-slate-500">{!! $preview['footer'] !!}</div>
            @endif
        </div>
    </div>
</x-layouts.admin>
