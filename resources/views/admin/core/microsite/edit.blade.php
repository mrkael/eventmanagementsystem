<x-layouts.admin title="Event Site" heading="Event Site" eyebrow="Event Details">
    <x-ui.page-header
        eyebrow="Site builder"
        title="{{ $event->title }}"
        description="Build the default event site structure: Header, Ticket & Form, and Footer. Double click any section to edit it."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.show', $event) }}" class="ds-button-secondary">Event Details</a>
            @if($event->custom_url && $event->is_public)
                <a target="_blank" href="{{ route('core.public.events.show', ['event' => $event->custom_url]) }}" class="ds-button-secondary">Open Public Site</a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'site'])

    @if($errors->has('site'))
        <div class="mb-5 rounded-[20px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-bold">Site cannot be published yet.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->get('site') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="site-publish-form" method="POST" action="{{ route('core.events.microsite.publish', $event) }}">
        @csrf
    </form>

    @php
        $ticketPreviewData = $visibleTickets->map(fn ($ticket) => [
            'id' => $ticket->id,
            'name' => $ticket->name,
            'description' => $ticket->description,
            'quantity' => $ticket->quantity,
            'available_quantity' => $ticket->available_quantity,
            'min_quantity' => $ticket->min_quantity,
            'max_quantity' => $ticket->max_quantity,
            'form' => $ticket->form ? [
                'id' => $ticket->form->id,
                'title' => $ticket->form->title,
                'fields' => $ticket->form->fields->map(fn ($field) => [
                    'label' => $field->label,
                    'type' => $field->type,
                    'placeholder' => $field->placeholder,
                    'is_required' => $field->is_required,
                    'options' => $field->options ?: [],
                ])->values(),
            ] : null,
        ])->values();

        $sectionData = $page->sections->map(fn ($section) => [
            'type' => $section->type,
            'title' => $section->title,
            'content' => $section->content,
            'settings' => $section->settings,
        ])->values();

        $defaultSections = collect(app(\App\Services\Core\MicrositeService::class)->defaultSections($event));
    @endphp

    <form method="POST" action="{{ route('core.events.microsite.update', $event) }}" data-site-builder
        data-tickets='@json($ticketPreviewData)'
        data-default-sections='@json($defaultSections)'
        data-current-sections='@json($sectionData)'>
        @csrf
        @method('PUT')
        <input type="hidden" name="sections" data-sections-json value="{{ old('sections', $sectionData->toJson()) }}">

        <div class="mb-5 grid gap-4 xl:grid-cols-[1fr_auto]">
            <x-ui.card>
                <label class="block">
                    <span class="ds-label">Template Name</span>
                    <input name="template" value="{{ old('template', $page->template ?: 'Default Event Site') }}" required class="ds-input mt-2" placeholder="Default Event Site">
                    @error('template')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
            </x-ui.card>
            <x-ui.card>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <button class="ds-button-primary justify-center" type="submit">Save Draft</button>
                    <a href="{{ route('core.events.microsite.preview', $event) }}" target="_blank" class="ds-button-secondary justify-center">Preview Site</a>
                    <button class="ds-button-secondary justify-center" type="submit" form="site-publish-form">Publish Site</button>
                    <button class="ds-button-secondary justify-center" type="button" data-reset-default>Reset Default</button>
                </div>
                <p class="mt-3 max-w-md text-sm leading-6 text-slate-500">Default structure: Header, Ticket & Form, Footer. Ticket & Form is required for registration.</p>
            </x-ui.card>
        </div>

        <div class="grid gap-5 xl:grid-cols-[1fr_18rem]">
            <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-soft">
                <div class="border-b border-slate-200 bg-white px-5 py-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-black text-slate-950">Event Site Canvas</h2>
                            <p class="mt-1 text-sm text-slate-500">Double click a section to edit. Ticket & Form stays connected to event tickets and assigned forms.</p>
                        </div>
                        <button type="button" data-add-row class="ds-button-secondary">Add New Row</button>
                    </div>
                </div>
                <div data-canvas class="min-h-[640px] bg-slate-50 p-5"></div>
            </div>

            <aside class="space-y-4 xl:sticky xl:top-24 xl:self-start">
                <x-ui.card>
                    <h3 class="text-base font-black text-slate-950">Add Content</h3>
                    <div class="mt-4 space-y-2">
                        <button type="button" data-add-section="text_content" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-left text-sm font-bold text-slate-700 hover:bg-slate-50">Text Content</button>
                        <button type="button" data-add-section="image" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-left text-sm font-bold text-slate-700 hover:bg-slate-50">Image</button>
                        <button type="button" data-add-section="button_cta" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-left text-sm font-bold text-slate-700 hover:bg-slate-50">Button / CTA</button>
                        <button type="button" data-add-section="agenda" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-left text-sm font-bold text-slate-700 hover:bg-slate-50">Agenda</button>
                        <button type="button" data-add-section="venue" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-left text-sm font-bold text-slate-700 hover:bg-slate-50">Venue</button>
                        <button type="button" data-add-section="faq" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-left text-sm font-bold text-slate-700 hover:bg-slate-50">FAQ</button>
                        <button type="button" data-add-section="sponsors" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-left text-sm font-bold text-slate-700 hover:bg-slate-50">Sponsor Logo</button>
                    </div>
                </x-ui.card>
            </aside>
        </div>
        @error('sections')<span class="mt-3 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror

        <div data-editor-panel class="fixed inset-0 z-50 hidden bg-slate-950/50 p-4 backdrop-blur-sm">
            <div class="ml-auto flex h-full max-w-xl flex-col rounded-[28px] bg-white shadow-2xl">
                <div class="border-b border-slate-200 p-5">
                    <p class="text-xs font-black uppercase text-blue-600">Section editor</p>
                    <h3 data-editor-heading class="mt-1 text-xl font-black text-slate-950">Edit Section</h3>
                </div>
                <div class="flex-1 space-y-4 overflow-y-auto p-5">
                    <label class="block">
                        <span class="ds-label">Title</span>
                        <input data-edit-title class="ds-input mt-2">
                    </label>
                    <label class="block">
                        <span class="ds-label">Content</span>
                        <textarea data-edit-content rows="7" class="ds-input mt-2 py-3"></textarea>
                    </label>
                    <label data-image-field class="block">
                        <span class="ds-label">Image URL</span>
                        <input data-edit-image class="ds-input mt-2" placeholder="https://...">
                    </label>
                    <label data-button-field class="block">
                        <span class="ds-label">Button Label</span>
                        <input data-edit-button-label class="ds-input mt-2" placeholder="Register now">
                    </label>
                    <label data-button-field class="block">
                        <span class="ds-label">Button Link</span>
                        <input data-edit-button-url class="ds-input mt-2" placeholder="#tickets">
                    </label>
                </div>
                <div class="flex gap-3 border-t border-slate-200 p-5">
                    <button type="button" data-save-edit class="ds-button-primary flex-1 justify-center">Apply Changes</button>
                    <button type="button" data-close-edit class="ds-button-secondary flex-1 justify-center">Cancel</button>
                </div>
            </div>
        </div>
    </form>

    <script>
        (() => {
            const root = document.querySelector('[data-site-builder]');
            if (!root) return;

            const tickets = JSON.parse(root.dataset.tickets || '[]');
            const defaults = JSON.parse(root.dataset.defaultSections || '[]');
            const current = JSON.parse(root.dataset.currentSections || '[]');
            const canvas = root.querySelector('[data-canvas]');
            const field = root.querySelector('[data-sections-json]');
            const panel = root.querySelector('[data-editor-panel]');
            let sections = current.length ? current : defaults;
            let editingIndex = null;

            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
            const text = (value) => escapeHtml(value || '').replace(/\n/g, '<br>');
            const ensureCore = () => {
                if (!sections.some((section) => section.type === 'ticket_selection')) {
                    sections.splice(Math.max(1, sections.length - 1), 0, { type: 'ticket_selection', title: 'Ticket & Form', content: 'Choose your ticket. The linked registration form will appear on this page.', settings: {} });
                }
                if (!sections.some((section) => section.type === 'registration_form')) {
                    const ticketIndex = sections.findIndex((section) => section.type === 'ticket_selection');
                    sections.splice(ticketIndex + 1, 0, { type: 'registration_form', title: 'Registration Form', content: 'The form shown here follows the selected ticket.', settings: {} });
                }
                if (!sections.some((section) => section.type === 'footer')) {
                    sections.push({ type: 'footer', title: '{{ addslashes($event->title) }}', content: '{{ addslashes($event->location ?: 'Event details will be updated soon.') }}', settings: {} });
                }
            };

            const ticketHtml = () => {
                if (!tickets.length) {
                    return '<div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm font-bold text-amber-800">No active visible tickets are available yet. Activate a ticket to show it automatically here.</div>';
                }
                return tickets.map((ticket) => `
                    <article class="rounded-2xl border border-slate-200 bg-white p-5">
                        <h3 class="font-black text-slate-950">${escapeHtml(ticket.name)}</h3>
                        <p class="mt-2 text-sm text-slate-500">${escapeHtml(ticket.description || 'No description')}</p>
                        <div class="mt-4 grid grid-cols-3 gap-3 text-xs font-bold text-slate-500">
                            <span>Available<br><strong class="text-slate-950">${ticket.available_quantity} / ${ticket.quantity}</strong></span>
                            <span>Min<br><strong class="text-slate-950">${ticket.min_quantity}</strong></span>
                            <span>Max<br><strong class="text-slate-950">${ticket.max_quantity}</strong></span>
                        </div>
                        <button type="button" class="mt-5 rounded-full bg-slate-950 px-4 py-2 text-sm font-bold text-white">Select ticket</button>
                    </article>
                `).join('');
            };

            const formHtml = () => {
                const ticket = tickets[0];
                if (!ticket?.form) {
                    return '<div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm font-bold text-amber-800">Active tickets appear automatically. Assign a registration form to the ticket to preview fields here.</div>';
                }
                return (ticket.form.fields || []).map((item) => `
                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">${escapeHtml(item.label)} ${item.is_required ? '<span class="text-red-600">*</span>' : ''}</span>
                        <input disabled class="mt-2 min-h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm" placeholder="${escapeHtml(item.placeholder || item.label)}">
                    </label>
                `).join('');
            };

            const sectionMarkup = (section, index) => {
                if (section.type === 'registration_form') return '';
                if (section.type === 'hero') {
                    return `<section data-row="${index}" class="group cursor-pointer rounded-[28px] border border-slate-200 bg-slate-950 p-8 text-white shadow-sm transition hover:ring-2 hover:ring-blue-300">
                        <div class="flex justify-between gap-4"><span class="rounded-full bg-white/10 px-3 py-1 text-xs font-black uppercase">Header</span>${rowActions(index)}</div>
                        <h1 class="mt-8 max-w-3xl text-5xl font-black">${escapeHtml(section.title)}</h1>
                        <p class="mt-5 max-w-2xl text-lg text-white/75">${text(section.content)}</p>
                    </section>`;
                }
                if (section.type === 'ticket_selection') {
                    return `<section data-row="${index}" class="group cursor-pointer rounded-[28px] border border-slate-200 bg-white p-8 shadow-sm transition hover:ring-2 hover:ring-blue-300">
                        <div class="flex justify-between gap-4"><span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-black uppercase text-white">Ticket & Form</span>${rowActions(index, true)}</div>
                        <h2 class="mt-6 text-3xl font-black text-slate-950">${escapeHtml(section.title)}</h2>
                        <p class="mt-2 text-slate-500">${text(section.content)}</p>
                        <div class="mt-6 grid gap-4 lg:grid-cols-2">${ticketHtml()}</div>
                        <div class="mt-6 rounded-[24px] border border-slate-200 bg-slate-50 p-5">
                            <p class="text-sm font-black uppercase text-slate-500">Linked form preview</p>
                            <div class="mt-4 max-w-2xl space-y-4">${formHtml()}</div>
                        </div>
                    </section>`;
                }
                if (section.type === 'image') {
                    const image = section.settings?.image_url || 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=1400&q=80';
                    return `<section data-row="${index}" class="group cursor-pointer rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm transition hover:ring-2 hover:ring-blue-300">
                        <div class="mb-4 flex justify-between gap-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase text-slate-500">Image</span>${rowActions(index)}</div>
                        <img src="${escapeHtml(image)}" alt="${escapeHtml(section.title || 'Event image')}" class="aspect-[16/7] w-full rounded-[24px] object-cover">
                        <h2 class="mt-5 text-2xl font-black text-slate-950">${escapeHtml(section.title || 'Image')}</h2>
                    </section>`;
                }
                if (section.type === 'button_cta') {
                    return `<section data-row="${index}" class="group cursor-pointer rounded-[28px] border border-blue-100 bg-blue-50 p-8 text-center shadow-sm transition hover:ring-2 hover:ring-blue-300">
                        <div class="flex justify-between gap-4"><span class="rounded-full bg-blue-600 px-3 py-1 text-xs font-black uppercase text-white">CTA</span>${rowActions(index)}</div>
                        <h2 class="mt-6 text-3xl font-black text-slate-950">${escapeHtml(section.title || 'Ready to register?')}</h2>
                        <p class="mt-3 text-slate-600">${text(section.content || 'Select a ticket and complete the form on this page.')}</p>
                        <a href="${escapeHtml(section.settings?.button_url || '#tickets')}" class="mt-6 inline-flex rounded-full bg-slate-950 px-6 py-3 text-sm font-black text-white">${escapeHtml(section.settings?.button_label || 'View Tickets')}</a>
                    </section>`;
                }
                if (section.type === 'footer') {
                    return `<footer data-row="${index}" class="group cursor-pointer rounded-[28px] border border-slate-200 bg-white p-8 text-center shadow-sm transition hover:ring-2 hover:ring-blue-300">
                        <div class="flex justify-between gap-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase text-slate-500">Footer</span>${rowActions(index, true)}</div>
                        <h2 class="mt-5 text-2xl font-black text-slate-950">${escapeHtml(section.title)}</h2>
                        <p class="mt-2 text-slate-500">${text(section.content)}</p>
                    </footer>`;
                }
                return `<section data-row="${index}" class="group cursor-pointer rounded-[28px] border border-slate-200 bg-white p-8 shadow-sm transition hover:ring-2 hover:ring-blue-300">
                    <div class="flex justify-between gap-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase text-slate-500">${escapeHtml(section.type.replaceAll('_', ' '))}</span>${rowActions(index)}</div>
                    <h2 class="mt-6 text-3xl font-black text-slate-950">${escapeHtml(section.title)}</h2>
                    <p class="mt-4 leading-7 text-slate-600">${text(section.content)}</p>
                </section>`;
            };

            const rowActions = (index, locked = false) => `
                <div class="flex gap-2 opacity-0 transition group-hover:opacity-100">
                    <button type="button" data-move-up="${index}" class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-700">Up</button>
                    <button type="button" data-move-down="${index}" class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-700">Down</button>
                    ${locked ? '' : `<button type="button" data-remove="${index}" class="rounded-full border border-red-200 bg-white px-3 py-1 text-xs font-bold text-red-700">Remove</button>`}
                </div>
            `;

            const sync = () => field.value = JSON.stringify(sections);
            const render = () => {
                ensureCore();
                canvas.innerHTML = `<div class="mx-auto max-w-6xl space-y-5">${sections.map(sectionMarkup).join('')}</div>`;
                sync();
            };

            const addSection = (type = 'text_content') => {
                const footerIndex = sections.findIndex((section) => section.type === 'footer');
                const item = { type, title: type.replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase()), content: 'Double click to edit this section.', settings: {} };
                sections.splice(footerIndex >= 0 ? footerIndex : sections.length, 0, item);
                render();
            };

            const openEditor = (index) => {
                const section = sections[index];
                if (!section) return;
                editingIndex = index;
                panel.classList.remove('hidden');
                root.querySelector('[data-editor-heading]').textContent = `Edit ${section.type === 'ticket_selection' ? 'Ticket & Form' : section.type.replaceAll('_', ' ')}`;
                root.querySelector('[data-edit-title]').value = section.title || '';
                root.querySelector('[data-edit-content]').value = section.content || '';
                root.querySelector('[data-edit-image]').value = section.settings?.image_url || '';
                root.querySelector('[data-edit-button-label]').value = section.settings?.button_label || '';
                root.querySelector('[data-edit-button-url]').value = section.settings?.button_url || '';
                root.querySelectorAll('[data-image-field]').forEach((item) => item.classList.toggle('hidden', section.type !== 'image' && section.type !== 'hero'));
                root.querySelectorAll('[data-button-field]').forEach((item) => item.classList.toggle('hidden', section.type !== 'button_cta'));
            };

            root.addEventListener('click', (event) => {
                const add = event.target.closest('[data-add-section]')?.dataset.addSection;
                if (add) addSection(add);
                if (event.target.closest('[data-add-row]')) addSection('text_content');
                if (event.target.closest('[data-reset-default]')) {
                    sections = JSON.parse(JSON.stringify(defaults));
                    render();
                }
                const remove = event.target.closest('[data-remove]')?.dataset.remove;
                if (remove !== undefined) {
                    sections.splice(Number(remove), 1);
                    render();
                }
                const up = event.target.closest('[data-move-up]')?.dataset.moveUp;
                if (up !== undefined && Number(up) > 0) {
                    [sections[Number(up) - 1], sections[Number(up)]] = [sections[Number(up)], sections[Number(up) - 1]];
                    render();
                }
                const down = event.target.closest('[data-move-down]')?.dataset.moveDown;
                if (down !== undefined && Number(down) < sections.length - 1) {
                    [sections[Number(down) + 1], sections[Number(down)]] = [sections[Number(down)], sections[Number(down) + 1]];
                    render();
                }
                if (event.target.closest('[data-close-edit]') || event.target === panel) {
                    panel.classList.add('hidden');
                }
                if (event.target.closest('[data-save-edit]') && editingIndex !== null) {
                    const section = sections[editingIndex];
                    section.title = root.querySelector('[data-edit-title]').value;
                    section.content = root.querySelector('[data-edit-content]').value;
                    section.settings = section.settings || {};
                    section.settings.image_url = root.querySelector('[data-edit-image]').value;
                    section.settings.button_label = root.querySelector('[data-edit-button-label]').value;
                    section.settings.button_url = root.querySelector('[data-edit-button-url]').value;
                    panel.classList.add('hidden');
                    render();
                }
            });

            canvas.addEventListener('dblclick', (event) => {
                const row = event.target.closest('[data-row]');
                if (row) openEditor(Number(row.dataset.row));
            });

            root.addEventListener('submit', sync);
            render();
        })();
    </script>
</x-layouts.admin>
