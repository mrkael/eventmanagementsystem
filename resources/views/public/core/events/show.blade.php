<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $event->title }}</title>
    @include('partials.assets')
</head>
<body class="bg-white text-slate-950">
    @php
        $sections = $sections ?? $event->publishedPage?->sections ?? collect();
        $ticketData = $tickets->map(fn ($ticket) => [
            'id' => $ticket->id,
            'name' => $ticket->name,
            'description' => $ticket->description,
            'quantity' => $ticket->quantity,
            'available_quantity' => $ticket->available_quantity,
            'min_quantity' => $ticket->min_quantity,
            'max_quantity' => $ticket->max_quantity,
            'submit_url' => route('core.public.submit', ['event' => $event->custom_url, 'ticket' => $ticket]),
            'form' => $ticket->form ? [
                'id' => $ticket->form->id,
                'title' => $ticket->form->title,
                'fields' => $ticket->form->fields->map(fn ($field) => [
                    'key' => $field->key,
                    'label' => $field->label,
                    'type' => $field->type,
                    'placeholder' => $field->placeholder,
                    'is_required' => $field->is_required,
                    'options' => $field->options ?: [],
                ])->values(),
            ] : null,
        ])->values();
    @endphp

    @if($isPreview ?? false)
        <div class="sticky top-0 z-50 border-b border-amber-200 bg-amber-50 px-4 py-3 text-center text-sm font-black text-amber-900">
            Site Preview - draft content, active tickets, and linked forms are shown for admin review.
        </div>
    @endif

    <main data-public-event data-tickets='@json($ticketData)'>
        @forelse($sections as $section)
            @if($section->type === 'registration_form')
                @continue
            @endif

            @if($section->type === 'hero')
                <section class="px-4 py-10 md:py-14">
                    <div class="mx-auto max-w-6xl rounded-[32px] bg-slate-950 px-6 py-12 text-white md:px-10 md:py-16">
                        <p class="text-sm font-black uppercase text-blue-200">Event</p>
                        <h1 class="mt-4 max-w-4xl text-4xl font-black tracking-normal md:text-6xl">{{ $section->title ?: $event->title }}</h1>
                        <p class="mt-5 max-w-2xl text-lg leading-8 text-white/75">{{ strip_tags($section->content) ?: $event->description }}</p>
                        <p class="mt-6 text-sm font-bold text-white/75">{{ $event->starts_at?->format('d M Y, H:i') }} - {{ $event->location }}</p>
                    </div>
                </section>
            @elseif($section->type === 'ticket_selection')
                <section id="tickets" class="px-4 py-10">
                    <div class="mx-auto max-w-6xl rounded-[28px] bg-slate-50 p-6 md:p-8">
                        <div>
                            <p class="text-xs font-black uppercase text-blue-600">Ticket & Form</p>
                            <h2 class="mt-2 text-3xl font-black text-slate-950">{{ $section->title ?: 'Ticket & Form' }}</h2>
                            <p class="mt-2 max-w-3xl text-slate-500">{{ strip_tags($section->content) ?: 'Choose your ticket. The linked registration form will appear on this page.' }}</p>
                        </div>

                        <div class="mt-6 grid gap-4 lg:grid-cols-2">
                            @forelse($tickets as $ticket)
                                <article class="rounded-2xl border border-slate-200 bg-white p-5">
                                    <h3 class="font-black text-slate-950">{{ $ticket->name }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ $ticket->description ?: 'No description' }}</p>
                                    <div class="mt-4 grid grid-cols-3 gap-3 text-xs font-bold text-slate-500">
                                        <span>Available<br><strong class="text-slate-950">{{ $ticket->available_quantity }} / {{ $ticket->quantity }}</strong></span>
                                        <span>Min<br><strong class="text-slate-950">{{ $ticket->min_quantity }}</strong></span>
                                        <span>Max<br><strong class="text-slate-950">{{ $ticket->max_quantity }}</strong></span>
                                    </div>
                                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                        <label class="flex-1">
                                            <span class="text-xs font-black uppercase text-slate-500">Quantity</span>
                                            <select data-ticket-quantity="{{ $ticket->id }}" class="mt-2 min-h-11 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm font-bold">
                                                @for($quantity = $ticket->min_quantity; $quantity <= min($ticket->max_quantity, $ticket->available_quantity); $quantity++)
                                                    <option value="{{ $quantity }}">{{ $quantity }}</option>
                                                @endfor
                                            </select>
                                        </label>
                                        <button type="button" data-select-ticket="{{ $ticket->id }}" class="self-end rounded-full bg-slate-950 px-5 py-3 text-sm font-black text-white">Select Ticket</button>
                                    </div>
                                </article>
                            @empty
                                <p class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm font-bold text-amber-800">No active tickets are currently available.</p>
                            @endforelse
                        </div>

                        <div data-registration-form class="mt-6 rounded-[24px] border border-slate-200 bg-white p-5">
                            <p class="text-sm font-bold text-slate-500">Select a ticket above to display its registration form here.</p>
                        </div>
                    </div>
                </section>
            @elseif($section->type === 'footer')
                <footer class="px-4 py-10">
                    <div class="mx-auto max-w-6xl rounded-[28px] border border-slate-200 bg-white p-8 text-center">
                        <h2 class="text-2xl font-black text-slate-950">{{ $section->title ?: $event->title }}</h2>
                        <p class="mt-2 text-slate-500">{{ strip_tags($section->content) ?: $event->location }}</p>
                    </div>
                </footer>
            @elseif($section->type === 'image')
                <section class="px-4 py-10">
                    <div class="mx-auto max-w-6xl">
                        @if($section->settings['image_url'] ?? null)
                            <img src="{{ $section->settings['image_url'] }}" alt="{{ $section->title }}" class="aspect-[16/7] w-full rounded-[28px] object-cover">
                        @endif
                    </div>
                </section>
            @elseif($section->type === 'button_cta')
                <section class="px-4 py-10">
                    <div class="mx-auto max-w-6xl rounded-[28px] bg-blue-50 p-8 text-center">
                        <h2 class="text-3xl font-black text-slate-950">{{ $section->title }}</h2>
                        <p class="mt-3 text-slate-600">{{ strip_tags($section->content) }}</p>
                        <a href="{{ $section->settings['button_url'] ?? '#tickets' }}" class="mt-6 inline-flex rounded-full bg-slate-950 px-6 py-3 text-sm font-black text-white">{{ $section->settings['button_label'] ?? 'View Tickets' }}</a>
                    </div>
                </section>
            @else
                <section class="px-4 py-10">
                    <div class="mx-auto max-w-6xl rounded-[28px] border border-slate-200 bg-white p-8">
                        <h2 class="text-3xl font-black text-slate-950">{{ $section->title }}</h2>
                        <p class="mt-4 whitespace-pre-line leading-7 text-slate-600">{{ strip_tags($section->content) }}</p>
                    </div>
                </section>
            @endif
        @empty
            <section class="px-4 py-16">
                <div class="mx-auto max-w-5xl">
                    <h1 class="text-4xl font-bold">{{ $event->title }}</h1>
                    <p class="mt-4 text-slate-600">{{ $event->description }}</p>
                </div>
            </section>
        @endforelse
    </main>

    <script>
        (() => {
            const root = document.querySelector('[data-public-event]');
            if (!root) return;

            const tickets = JSON.parse(root.dataset.tickets || '[]');
            const formTarget = root.querySelector('[data-registration-form]');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));

            const fieldHtml = (field, attendeeIndex) => {
                const key = field.key || field.label.toLowerCase().replace(/[^a-z0-9]+/g, '_');
                const name = `participants[${attendeeIndex}][${key}]`;
                const required = field.is_required ? 'required' : '';
                const label = `<span class="text-sm font-bold text-slate-700">${escapeHtml(field.label)} ${field.is_required ? '<span class="text-red-600">*</span>' : ''}</span>`;
                const base = `name="${escapeHtml(name)}" ${required} class="mt-2 min-h-11 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm" placeholder="${escapeHtml(field.placeholder || field.label)}"`;

                if (field.type === 'textarea') {
                    return `<label class="block">${label}<textarea ${base}></textarea></label>`;
                }
                if (['dropdown', 'radio', 'checkbox'].includes(field.type)) {
                    const options = Array.isArray(field.options) ? field.options : [];
                    if (field.type === 'dropdown') {
                        return `<label class="block">${label}<select ${base}>${options.map((option) => `<option>${escapeHtml(option)}</option>`).join('')}</select></label>`;
                    }
                    return `<fieldset class="rounded-2xl border border-slate-200 p-4"><legend class="px-1 text-sm font-bold text-slate-700">${escapeHtml(field.label)}</legend><div class="mt-3 space-y-2">${options.map((option) => `<label class="flex items-center gap-2 text-sm text-slate-700"><input type="${field.type}" name="${escapeHtml(name)}${field.type === 'checkbox' ? '[]' : ''}" value="${escapeHtml(option)}" ${required}> ${escapeHtml(option)}</label>`).join('')}</div></fieldset>`;
                }
                const type = ['email', 'number', 'date', 'file'].includes(field.type) ? field.type : 'text';
                return `<label class="block">${label}<input type="${type}" ${base}></label>`;
            };

            const renderForm = (ticket, quantity) => {
                if (!ticket?.form) {
                    formTarget.innerHTML = '<p class="text-sm font-bold text-amber-800">This ticket does not have a registration form assigned.</p>';
                    return;
                }

                formTarget.innerHTML = `
                    <form method="POST" action="${escapeHtml(ticket.submit_url)}" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="${escapeHtml(csrf)}">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-xs font-black uppercase text-blue-600">Registration Form</p>
                                <h3 class="mt-1 text-2xl font-black text-slate-950">${escapeHtml(ticket.form.title)}</h3>
                                <p class="mt-1 text-sm text-slate-500">${escapeHtml(ticket.name)} - ${quantity} ticket${quantity > 1 ? 's' : ''}</p>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black uppercase text-emerald-700">Ready</span>
                        </div>
                        <div class="mt-6 space-y-8">
                            ${Array.from({ length: quantity }, (_, attendeeIndex) => `
                                <section class="rounded-[24px] border border-slate-200 bg-slate-50 p-5">
                                    <h4 class="font-black text-slate-950">Participant ${attendeeIndex + 1}</h4>
                                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                                        ${(ticket.form.fields || []).map((field) => fieldHtml(field, attendeeIndex)).join('')}
                                    </div>
                                </section>
                            `).join('')}
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="rounded-full bg-slate-950 px-6 py-3 text-sm font-black text-white">Submit Registration</button>
                        </div>
                    </form>
                `;
                formTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
            };

            root.addEventListener('click', (event) => {
                const button = event.target.closest('[data-select-ticket]');
                if (!button) return;
                const ticket = tickets.find((item) => String(item.id) === String(button.dataset.selectTicket));
                const quantity = Number(root.querySelector(`[data-ticket-quantity="${button.dataset.selectTicket}"]`)?.value || ticket?.min_quantity || 1);
                renderForm(ticket, quantity);
            });
        })();
    </script>
</body>
</html>
