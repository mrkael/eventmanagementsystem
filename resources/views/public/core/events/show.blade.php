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
        $oldTicketId = old('selected_ticket_id');
        $oldQuantity = old('ticket_quantity', 1);
        $oldParticipants = old('participants', []);
        $siteHtml = function (?string $content): string {
            $content = preg_replace('#<(script|style|iframe|object|embed)[^>]*>.*?</\1>#is', '', (string) $content) ?? '';
            $content = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $content) ?? '';
            $content = preg_replace('/(href|src)\s*=\s*([\'"])\s*javascript:[^\'"]*\2/i', '$1="#"', $content) ?? '';
            $content = preg_replace_callback('/\sstyle\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', function (array $matches) {
                $style = trim($matches[1], '"\'');

                return preg_match('/^text-align\s*:\s*(left|center|right|justify)\s*;?$/i', $style)
                    ? ' style="'.e($style).'"'
                    : '';
            }, $content) ?? '';
            $content = preg_replace('/\s(?!href\s*=|src\s*=|alt\s*=|title\s*=|target\s*=|rel\s*=|style\s*=)[a-z0-9:_-]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $content) ?? '';
            return trim(strip_tags($content, '<p><br><strong><b><em><i><u><h1><h2><h3><h4><ul><ol><li><a><img><div><span><blockquote>'));
        };
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

    <main data-public-event data-tickets='@json($ticketData)' data-old-ticket-id="{{ $oldTicketId }}" data-old-quantity="{{ $oldQuantity }}" data-old-participants='@json($oldParticipants)'>
        @forelse($sections as $section)
            @if($section->type === 'registration_form')
                @continue
            @endif

            @if($section->type === 'hero')
                <section class="px-4 py-10 md:py-14">
                    <div class="mx-auto max-w-6xl rounded-[32px] bg-slate-950 px-6 py-12 text-white md:px-10 md:py-16">
                        <p class="text-sm font-black uppercase text-blue-200">Event</p>
                        <h1 class="mt-4 max-w-4xl text-4xl font-black tracking-normal md:text-6xl">{{ $section->title ?: $event->title }}</h1>
                        <div class="mt-5 max-w-2xl space-y-3 text-lg leading-8 text-white/75 [&_a]:font-bold [&_a]:text-blue-200 [&_img]:rounded-2xl [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6">{!! $siteHtml($section->content) ?: e($event->description) !!}</div>
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

                        @if($errors->any())
                            <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-800">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        @if(session('status'))
                            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">
                                {{ session('status') }}
                            </div>
                        @endif

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
                        <div class="mt-2 space-y-2 text-slate-500 [&_a]:font-bold [&_a]:text-blue-700 [&_img]:mx-auto [&_img]:rounded-2xl [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6">{!! $siteHtml($section->content) ?: e($event->location) !!}</div>
                    </div>
                </footer>
            @elseif($section->type === 'image')
                <section class="px-4 py-10">
                    <div class="mx-auto max-w-6xl">
                        @if($section->settings['image_url'] ?? null)
                            <img src="{{ $section->settings['image_url'] }}" alt="{{ $section->title }}" class="aspect-[16/7] w-full rounded-[28px] object-cover">
                        @endif
                        @if($siteHtml($section->content))
                            <div class="mt-4 space-y-3 leading-7 text-slate-600 [&_a]:font-bold [&_a]:text-blue-700 [&_img]:rounded-2xl [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6">{!! $siteHtml($section->content) !!}</div>
                        @endif
                    </div>
                </section>
            @elseif($section->type === 'button_cta')
                <section class="px-4 py-10">
                    <div class="mx-auto max-w-6xl rounded-[28px] bg-blue-50 p-8 text-center">
                        <h2 class="text-3xl font-black text-slate-950">{{ $section->title }}</h2>
                        <div class="mt-3 space-y-3 text-slate-600 [&_a]:font-bold [&_a]:text-blue-700 [&_img]:mx-auto [&_img]:rounded-2xl [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6">{!! $siteHtml($section->content) !!}</div>
                        <a href="{{ $section->settings['button_url'] ?? '#tickets' }}" class="mt-6 inline-flex rounded-full bg-slate-950 px-6 py-3 text-sm font-black text-white">{{ $section->settings['button_label'] ?? 'View Tickets' }}</a>
                    </div>
                </section>
            @else
                <section class="px-4 py-10">
                    <div class="mx-auto max-w-6xl rounded-[28px] border border-slate-200 bg-white p-8">
                        <h2 class="text-3xl font-black text-slate-950">{{ $section->title }}</h2>
                        <div class="mt-4 space-y-3 leading-7 text-slate-600 [&_a]:font-bold [&_a]:text-blue-700 [&_img]:rounded-2xl [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6">{!! $siteHtml($section->content) !!}</div>
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
            const uploadIcon = '<svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4" /><path d="m7 9 5-5 5 5" /><path d="M5 20h14" /></svg>';
            const successIcon = '<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5" /></svg>';
            const loadingIcon = '<svg class="size-6 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M21 12a9 9 0 0 0-9-9v3a6 6 0 0 1 6 6z"></path></svg>';

            const fileUploadHtml = (field, attendeeIndex, name, required) => {
                const uploadId = `upload_${attendeeIndex}_${String(field.key || field.label).replace(/[^a-z0-9]+/gi, '_')}`;

                return `
                    <div data-file-upload class="block">
                        <span class="text-sm font-bold text-slate-700">${escapeHtml(field.label)} ${field.is_required ? '<span class="text-red-600">*</span>' : ''}</span>
                        <label data-upload-dropzone for="${escapeHtml(uploadId)}" class="mt-2 block cursor-pointer rounded-[24px] border border-dashed border-slate-300 bg-slate-50 p-6 text-center transition hover:border-blue-300 hover:bg-blue-50">
                            <input id="${escapeHtml(uploadId)}" type="file" name="${escapeHtml(name)}" ${required} data-file-input class="sr-only" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <div data-upload-default>
                                <div class="mx-auto grid size-12 place-items-center rounded-2xl bg-white text-slate-500 shadow-sm">${uploadIcon}</div>
                                <p class="mt-3 text-sm font-bold text-slate-700">Click to upload or drag and drop</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">${escapeHtml(field.placeholder || 'Browse/select file')}</p>
                                <p class="mt-3 text-xs font-semibold text-slate-400">PDF, JPG, PNG, DOC, DOCX up to 10MB</p>
                            </div>
                            <div data-upload-loading class="hidden">
                                <div class="mx-auto grid size-12 place-items-center rounded-2xl bg-white text-blue-700 shadow-sm">${loadingIcon}</div>
                                <p data-upload-loading-name class="mt-3 truncate text-sm font-bold text-slate-700"></p>
                                <p class="mt-1 text-xs font-semibold text-blue-700">Uploading file...</p>
                                <div class="mx-auto mt-4 h-2 max-w-xs overflow-hidden rounded-full bg-slate-200">
                                    <div data-upload-progress class="h-full w-0 rounded-full bg-blue-700 transition-all duration-300"></div>
                                </div>
                            </div>
                            <div data-upload-success class="hidden">
                                <div class="mx-auto grid size-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-700">${successIcon}</div>
                                <p data-upload-file-name class="mt-3 truncate text-sm font-bold text-slate-800"></p>
                                <p class="mt-1 text-xs font-semibold text-emerald-700">File ready to submit</p>
                            </div>
                        </label>
                        <div data-upload-actions class="mt-3 hidden items-center gap-2">
                            <button type="button" data-replace-file class="rounded-full border border-slate-200 px-4 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">Replace file</button>
                            <button type="button" data-remove-file class="rounded-full border border-red-200 px-4 py-2 text-xs font-black text-red-700 hover:bg-red-50">Remove file</button>
                        </div>
                        ${field.error_text ? `<span class="mt-2 block text-xs font-semibold text-slate-500">${escapeHtml(field.error_text)}</span>` : ''}
                    </div>
                `;
            };

            const fieldHtml = (field, attendeeIndex) => {
                const key = field.key || field.label.toLowerCase().replace(/[^a-z0-9]+/g, '_');
                const name = `participants[${attendeeIndex}][${key}]`;
                const required = field.is_required ? 'required' : '';
                const label = `<span class="text-sm font-bold text-slate-700">${escapeHtml(field.label)} ${field.is_required ? '<span class="text-red-600">*</span>' : ''}</span>`;
                const base = `name="${escapeHtml(name)}" ${required} class="mt-2 min-h-11 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm" placeholder="${escapeHtml(field.placeholder || field.label)}"`;

                if (field.type === 'textarea') {
                    return `<label class="block">${label}<textarea ${base}></textarea></label>`;
                }
                if (field.type === 'file') {
                    return fileUploadHtml(field, attendeeIndex, name, required);
                }
                if (['dropdown', 'radio', 'checkbox'].includes(field.type)) {
                    const options = Array.isArray(field.options) ? field.options : [];
                    if (field.type === 'dropdown') {
                        return `<label class="block">${label}<select ${base}>${options.map((option) => `<option>${escapeHtml(option)}</option>`).join('')}</select></label>`;
                    }
                    return `<fieldset class="rounded-2xl border border-slate-200 p-4"><legend class="px-1 text-sm font-bold text-slate-700">${escapeHtml(field.label)} ${field.is_required ? '<span class="text-red-600">*</span>' : ''}</legend><div class="mt-3 space-y-2">${options.map((option) => `<label class="flex items-center gap-2 text-sm text-slate-700"><input type="${field.type}" name="${escapeHtml(name)}${field.type === 'checkbox' ? '[]' : ''}" value="${escapeHtml(option)}" ${field.type === 'radio' ? required : ''}> ${escapeHtml(option)}</label>`).join('')}</div>${field.type === 'checkbox' && field.is_required ? `<input type="text" class="sr-only" tabindex="-1" aria-hidden="true" data-checkbox-required="${escapeHtml(name)}" required>` : ''}</fieldset>`;
                }
                const type = ['email', 'number', 'date'].includes(field.type) ? field.type : 'text';
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
                        <input type="hidden" name="selected_ticket_id" value="${escapeHtml(ticket.id)}">
                        <input type="hidden" name="ticket_quantity" value="${escapeHtml(quantity)}">
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
                                    <div class="mt-4 space-y-4">
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

            root.addEventListener('change', (event) => {
                const fileInput = event.target.closest('[data-file-input]');
                if (fileInput) {
                    const wrapper = fileInput.closest('[data-file-upload]');
                    const file = fileInput.files?.[0];
                    const defaultState = wrapper.querySelector('[data-upload-default]');
                    const loadingState = wrapper.querySelector('[data-upload-loading]');
                    const successState = wrapper.querySelector('[data-upload-success]');
                    const loadingName = wrapper.querySelector('[data-upload-loading-name]');
                    const fileName = wrapper.querySelector('[data-upload-file-name]');
                    const progress = wrapper.querySelector('[data-upload-progress]');
                    const actions = wrapper.querySelector('[data-upload-actions]');

                    if (!file) return;
                    if (wrapper.dataset.uploading === 'true') return;

                    wrapper.dataset.uploading = 'true';
                    defaultState.classList.add('hidden');
                    successState.classList.add('hidden');
                    actions.classList.add('hidden');
                    actions.classList.remove('flex');
                    loadingState.classList.remove('hidden');
                    loadingName.textContent = file.name;
                    progress.style.width = '45%';

                    window.setTimeout(() => {
                        progress.style.width = '100%';
                    }, 120);

                    window.setTimeout(() => {
                        wrapper.dataset.uploading = 'false';
                        loadingState.classList.add('hidden');
                        successState.classList.remove('hidden');
                        fileName.textContent = file.name;
                        actions.classList.remove('hidden');
                        actions.classList.add('flex');
                    }, 520);

                    return;
                }

                const marker = event.target.closest('input[type="checkbox"]');
                if (!marker) return;
                const name = marker.name.replace(/\[\]$/, '');
                const requiredMarker = root.querySelector(`[data-checkbox-required="${CSS.escape(name)}"]`);
                if (!requiredMarker) return;
                requiredMarker.value = root.querySelectorAll(`input[name="${CSS.escape(marker.name)}"]:checked`).length ? 'selected' : '';
            });

            root.addEventListener('submit', (event) => {
                const form = event.target.closest('form');
                if (!form) return;
                const button = form.querySelector('button[type="submit"]');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
                if (button) {
                    button.disabled = true;
                    button.textContent = 'Submitting...';
                    button.classList.add('opacity-70', 'cursor-wait');
                }
            });

            root.addEventListener('dragover', (event) => {
                const dropzone = event.target.closest('[data-upload-dropzone]');
                if (!dropzone) return;
                event.preventDefault();
                dropzone.classList.add('border-blue-400', 'bg-blue-50');
            });

            root.addEventListener('dragleave', (event) => {
                const dropzone = event.target.closest('[data-upload-dropzone]');
                if (!dropzone) return;
                dropzone.classList.remove('border-blue-400', 'bg-blue-50');
            });

            root.addEventListener('drop', (event) => {
                const dropzone = event.target.closest('[data-upload-dropzone]');
                if (!dropzone) return;
                event.preventDefault();
                dropzone.classList.remove('border-blue-400', 'bg-blue-50');
                const input = dropzone.querySelector('[data-file-input]');
                if (!input || input.closest('[data-file-upload]')?.dataset.uploading === 'true' || !event.dataTransfer?.files?.length) return;
                input.files = event.dataTransfer.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });

            root.addEventListener('click', (event) => {
                const replaceFile = event.target.closest('[data-replace-file]');
                const removeFile = event.target.closest('[data-remove-file]');
                if (replaceFile || removeFile) {
                    const wrapper = event.target.closest('[data-file-upload]');
                    if (wrapper.dataset.uploading === 'true') return;
                    const fileInput = wrapper.querySelector('[data-file-input]');
                    const defaultState = wrapper.querySelector('[data-upload-default]');
                    const loadingState = wrapper.querySelector('[data-upload-loading]');
                    const successState = wrapper.querySelector('[data-upload-success]');
                    const actions = wrapper.querySelector('[data-upload-actions]');
                    const progress = wrapper.querySelector('[data-upload-progress]');

                    if (removeFile) {
                        fileInput.value = '';
                        wrapper.dataset.uploading = 'false';
                        progress.style.width = '0%';
                        loadingState.classList.add('hidden');
                        successState.classList.add('hidden');
                        defaultState.classList.remove('hidden');
                        actions.classList.add('hidden');
                        actions.classList.remove('flex');
                    }

                    if (replaceFile) {
                        fileInput.click();
                    }

                    return;
                }

                const button = event.target.closest('[data-select-ticket]');
                if (!button) return;
                const ticket = tickets.find((item) => String(item.id) === String(button.dataset.selectTicket));
                const quantity = Number(root.querySelector(`[data-ticket-quantity="${button.dataset.selectTicket}"]`)?.value || ticket?.min_quantity || 1);
                renderForm(ticket, quantity);
            });

            const oldTicketId = root.dataset.oldTicketId;
            if (oldTicketId) {
                const ticket = tickets.find((item) => String(item.id) === String(oldTicketId));
                renderForm(ticket, Number(root.dataset.oldQuantity || ticket?.min_quantity || 1));
            }
        })();
    </script>
</body>
</html>
