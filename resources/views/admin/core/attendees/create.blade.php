<x-layouts.admin title="Add Attendee" heading="Add Attendee" eyebrow="Event Details">
    <x-ui.page-header
        eyebrow="Step 1 of 2"
        title="Select ticket"
        description="Choose the ticket first. The assigned registration form will be loaded in the next step."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.attendees.index', $event) }}" class="ds-button-secondary">Back to Attendees</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'attendees'])

    @if($errors->any())
        <div class="mb-5 rounded-[20px] border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-800">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-4">
        @forelse($tickets as $ticket)
            @php($canContinue = $ticket->status === 'active' && $ticket->available_quantity > 0 && $ticket->form)
            <x-ui.card class="transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-black text-slate-950">{{ $ticket->name }}</h2>
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $ticket->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ str($ticket->status)->headline() }}</span>
                            @if($ticket->is_hidden)
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">Hidden Ticket</span>
                            @endif
                        </div>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">{{ $ticket->description ?: 'No description provided.' }}</p>
                        <div class="mt-4 grid gap-3 text-sm sm:grid-cols-4">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase text-slate-500">Quantity</p>
                                <p class="mt-1 font-black text-slate-950">{{ number_format($ticket->quantity) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase text-slate-500">Available</p>
                                <p class="mt-1 font-black text-slate-950">{{ number_format($ticket->available_quantity) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase text-slate-500">Assigned Form</p>
                                <p class="mt-1 font-black {{ $ticket->form ? 'text-slate-950' : 'text-red-700' }}">{{ $ticket->form?->title ?? 'Missing' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase text-slate-500">Form Fields</p>
                                <p class="mt-1 font-black text-slate-950">{{ $ticket->form?->fields->count() ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="lg:text-right">
                        @if($canContinue)
                            <a href="{{ route('core.events.attendees.register', [$event, $ticket]) }}" class="ds-button-primary justify-center">Continue</a>
                        @else
                            <button type="button" disabled class="ds-button-secondary justify-center opacity-50">Unavailable</button>
                            <p class="mt-2 max-w-56 text-xs font-semibold text-red-600">Ticket must be active, available, and assigned to a form.</p>
                        @endif
                    </div>
                </div>
            </x-ui.card>
        @empty
            <x-ui.empty-state icon="ticket" title="No tickets found" description="Create an active ticket and assign it to a form before adding attendees." />
        @endforelse
    </div>
</x-layouts.admin>
