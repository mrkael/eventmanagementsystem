<x-layouts.admin title="Attendee Detail" heading="Attendee Detail" eyebrow="Event Details">
    <x-ui.page-header
        eyebrow="Attendee"
        title="{{ $registration->full_name }}"
        description="{{ $registration->reference_number }} for {{ $event->title }}"
    >
        <x-slot:actions>
            <a href="{{ route('core.events.attendees.index', $event) }}" class="ds-button-secondary">Back to Attendees</a>
            <a href="{{ route('core.events.attendees.edit', [$event, $registration]) }}" class="ds-button-secondary">Edit</a>
            <form method="POST" action="{{ route('core.events.attendees.resend', [$event, $registration]) }}">
                @csrf
                <button class="ds-button-primary">Resend Email</button>
            </form>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'attendees'])

    <div class="grid gap-5 xl:grid-cols-[1fr_22rem]">
        <div class="space-y-5">
            <x-ui.card>
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <p class="ds-label">Registration Reference</p>
                        <p class="mt-2 font-black text-slate-950">{{ $registration->reference_number }}</p>
                    </div>
                    <div>
                        <p class="ds-label">Ticket</p>
                        <p class="mt-2 font-black text-slate-950">{{ $registration->ticket?->name }}</p>
                    </div>
                    <div>
                        <p class="ds-label">Participant Name</p>
                        <p class="mt-2 font-black text-slate-950">{{ $registration->full_name }}</p>
                    </div>
                    <div>
                        <p class="ds-label">Participant Email</p>
                        <p class="mt-2 font-black text-slate-950">{{ $registration->email }}</p>
                    </div>
                    <div>
                        <p class="ds-label">Phone</p>
                        <p class="mt-2 text-slate-700">{{ $registration->phone ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="ds-label">Organization</p>
                        <p class="mt-2 text-slate-700">{{ $registration->organization ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="ds-label">Designation</p>
                        <p class="mt-2 text-slate-700">{{ $registration->designation ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="ds-label">Registered By</p>
                        <p class="mt-2 text-slate-700">{{ $registration->registeredBy?->name ?? 'Public registration' }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="border-b border-slate-100 pb-5">
                    <p class="text-xs font-black uppercase text-blue-600">Registration Answers</p>
                    <h2 class="mt-1 text-xl font-black text-slate-950">{{ $registration->form?->title }}</h2>
                </div>
                <div class="mt-5 divide-y divide-slate-100">
                    @forelse($registration->answers as $answer)
                        <div class="grid gap-2 py-4 md:grid-cols-[14rem_1fr]">
                            <p class="font-bold text-slate-500">{{ $answer->field_label }}</p>
                            <p class="font-semibold text-slate-950">
                                @if($answer->file_path)
                                    {{ $answer->file_path }}
                                @elseif(is_array($answer->value))
                                    {{ implode(', ', $answer->value) }}
                                @else
                                    {{ $answer->value ?: '-' }}
                                @endif
                            </p>
                        </div>
                    @empty
                        <x-ui.empty-state icon="editor" title="No answers stored" description="No dynamic form answers are attached to this registration." />
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <aside class="space-y-5">
            <x-ui.card>
                <p class="ds-label">Status</p>
                <p class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-black {{ $registration->status === 'confirmed' ? 'bg-emerald-50 text-emerald-700' : ($registration->status === 'cancelled' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600') }}">{{ str($registration->status)->headline() }}</p>
                <div class="mt-5 space-y-4 text-sm">
                    <div>
                        <p class="font-bold text-slate-500">Confirmation Email</p>
                        <p class="mt-1 font-black text-slate-950">{{ $registration->confirmation_email_sent_at ? 'Sent '.$registration->confirmation_email_sent_at->format('d M Y, H:i') : 'Not sent' }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-slate-500">QR Status</p>
                        <p class="mt-1 font-black text-slate-950">{{ $registration->qr_token ? 'Generated' : 'Missing' }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-slate-500">Registered At</p>
                        <p class="mt-1 font-black text-slate-950">{{ $registration->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <form method="POST" action="{{ route('core.events.attendees.cancel', [$event, $registration]) }}">
                    @csrf
                    @method('PATCH')
                    <button @disabled($registration->status === 'cancelled') class="w-full rounded-full border border-red-200 px-4 py-3 text-sm font-black text-red-700 hover:bg-red-50 disabled:opacity-40">Cancel Registration</button>
                </form>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.admin>
