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
                            <p class="mt-1 text-sm text-slate-500">Click inside any normal content block to edit inline. Ticket & Form stays connected to event tickets and assigned forms.</p>
                        </div>
                        <button type="button" data-add-row class="ds-button-secondary">Add New Row</button>
                    </div>
                </div>
                <div data-canvas class="min-h-[640px] bg-slate-50 p-5"></div>
                <div class="flex justify-end border-t border-slate-200 bg-white px-5 py-4">
                    <button type="button" data-add-row class="ds-button-primary">Add New Row</button>
                </div>
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
            let sections = current.length ? current : defaults;
            let activeEditable = null;
            let editingIndex = null;

            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
            const sanitizeHtml = (value) => {
                const template = document.createElement('template');
                template.innerHTML = String(value ?? '');
                const allowedTags = new Set(['P', 'BR', 'STRONG', 'B', 'EM', 'I', 'U', 'H1', 'H2', 'H3', 'H4', 'UL', 'OL', 'LI', 'A', 'IMG', 'DIV', 'SPAN', 'BLOCKQUOTE']);
                const allowedAttrs = new Set(['href', 'src', 'alt', 'title', 'target', 'rel', 'class', 'style']);
                [...template.content.querySelectorAll('script, style, iframe, object, embed')].forEach((node) => node.remove());
                [...template.content.querySelectorAll('*')].forEach((node) => {
                    if (!allowedTags.has(node.tagName)) {
                        node.replaceWith(...node.childNodes);
                        return;
                    }
                    [...node.attributes].forEach((attr) => {
                        const name = attr.name.toLowerCase();
                        const value = attr.value.trim();
                        if (name.startsWith('on') || !allowedAttrs.has(name) || /javascript:/i.test(value) || (name === 'style' && !/^text-align\s*:\s*(left|center|right|justify)\s*;?$/i.test(value))) {
                            node.removeAttribute(attr.name);
                        }
                    });
                    if (node.tagName === 'A') {
                        node.setAttribute('rel', 'noopener noreferrer');
                        if (!node.getAttribute('target')) node.setAttribute('target', '_blank');
                    }
                    if (node.tagName === 'IMG') {
                        node.classList.add('rounded-2xl');
                    }
                });
                return template.innerHTML.trim();
            };
            const richText = (value) => sanitizeHtml(value || '').replace(/\n/g, '<br>');
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

            const isContentBlock = (section) => !['ticket_selection', 'registration_form'].includes(section.type);
            const isEditing = (index) => Number(editingIndex) === Number(index);
            const rowClasses = (index) => `group relative border border-slate-200 bg-white text-slate-950 transition hover:ring-1 hover:ring-blue-300 ${isEditing(index) ? 'ring-2 ring-blue-300' : ''}`;
            const rowToolbar = (index) => isEditing(index) ? `
                <div data-row-toolbar="${index}" class="border-b border-slate-200 bg-slate-50 p-2">
                    <div class="flex flex-wrap gap-1">
                        <button type="button" data-wysiwyg-format="p" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">P</button>
                        <button type="button" data-wysiwyg-format="h2" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">H2</button>
                        <button type="button" data-wysiwyg-format="h3" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">H3</button>
                        <button type="button" data-wysiwyg-command="bold" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">B</button>
                        <button type="button" data-wysiwyg-command="italic" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black italic text-slate-700 hover:bg-slate-50">I</button>
                        <button type="button" data-wysiwyg-command="underline" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black underline text-slate-700 hover:bg-slate-50">U</button>
                        <button type="button" data-wysiwyg-command="insertUnorderedList" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">List</button>
                        <button type="button" data-wysiwyg-command="insertOrderedList" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">1. List</button>
                        <button type="button" data-wysiwyg-command="justifyLeft" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">Left</button>
                        <button type="button" data-wysiwyg-command="justifyCenter" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">Center</button>
                        <button type="button" data-wysiwyg-command="justifyRight" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">Right</button>
                        <button type="button" data-wysiwyg-link class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">Link</button>
                        <button type="button" data-wysiwyg-image class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">Image</button>
                        <button type="button" data-wysiwyg-source class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">Source</button>
                        <button type="button" data-close-row-editor="${index}" class="ml-auto rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">Done</button>
                    </div>
                    <textarea data-html-source="${index}" rows="5" class="ds-input mt-3 hidden font-mono text-xs" spellcheck="false"></textarea>
                </div>
            ` : '';
            const titleBlock = (section, index, classes) => isEditing(index) && isContentBlock(section)
                ? `<div data-editable-title="${index}" contenteditable="true" class="${classes} outline-none">${escapeHtml(section.title || '')}</div>`
                : `<div class="${classes}">${escapeHtml(section.title || '')}</div>`;
            const contentBlock = (section, index, classes, fallback = '') => isEditing(index) && isContentBlock(section)
                ? `<div data-editable-content="${index}" contenteditable="true" class="${classes} min-h-64 outline-none">${richText(section.content || fallback)}</div>`
                : `<div class="${classes}">${richText(section.content || fallback)}</div>`;
            const syncRow = (row) => {
                const index = Number(row?.dataset.row);
                const section = sections[index];
                if (!section || !isContentBlock(section)) return;
                const title = row.querySelector(`[data-editable-title="${index}"]`);
                const content = row.querySelector(`[data-editable-content="${index}"]`);
                const image = row.querySelector(`[data-setting-image="${index}"]`);
                const buttonLabel = row.querySelector(`[data-setting-button-label="${index}"]`);
                const buttonUrl = row.querySelector(`[data-setting-button-url="${index}"]`);
                if (title) section.title = title.textContent.trim();
                if (content) section.content = sanitizeHtml(content.innerHTML);
                section.settings = section.settings || {};
                if (image) section.settings.image_url = image.value;
                if (buttonLabel) section.settings.button_label = buttonLabel.value;
                if (buttonUrl) section.settings.button_url = buttonUrl.value;
                sync();
            };
            const syncCanvas = () => {
                canvas.querySelectorAll('[data-row]').forEach(syncRow);
                sync();
            };

            const sectionMarkup = (section, index) => {
                if (section.type === 'registration_form') return '';
                if (section.type === 'hero') {
                    return `<section data-row="${index}" data-editable-row class="${rowClasses(index)}">
                        ${rowToolbar(index)}
                        <div class="p-4">
                            <div class="flex justify-end">${rowActions(index)}</div>
                            ${titleBlock(section, index, 'text-xl font-black uppercase text-slate-950')}
                            ${contentBlock(section, index, 'mt-2 leading-7 text-slate-500')}
                        </div>
                    </section>`;
                }
                if (section.type === 'ticket_selection') {
                    return `<section data-row="${index}" class="${rowClasses(index)}">
                        <div class="flex items-start justify-between gap-4 p-4">
                            <div class="flex-1 text-center">
                                <h2 class="text-2xl font-black text-slate-950">${escapeHtml(section.title)}</h2>
                                <p class="mt-2 text-sm text-slate-500">${escapeHtml(section.content)}</p>
                            </div>
                            ${rowActions(index, true)}
                        </div>
                        <div class="border-t border-slate-200 p-4">
                            <div class="grid gap-4 lg:grid-cols-2">${ticketHtml()}</div>
                            <div class="mt-4 rounded-[20px] border border-slate-200 bg-white p-4">
                                <p class="text-sm font-black uppercase text-slate-500">Linked form preview</p>
                                <div class="mt-4 max-w-2xl space-y-4">${formHtml()}</div>
                            </div>
                        </div>
                    </section>`;
                }
                if (section.type === 'image') {
                    const image = section.settings?.image_url || 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=1400&q=80';
                    return `<section data-row="${index}" data-editable-row class="${rowClasses(index)}">
                        ${rowToolbar(index)}
                        <div class="p-3">
                            <div class="flex justify-end">${rowActions(index)}</div>
                            <div class="relative overflow-hidden">
                                <img src="${escapeHtml(image)}" alt="${escapeHtml(section.title || 'Event image')}" class="aspect-[16/7] w-full object-cover">
                                <div class="absolute bottom-0 left-0 p-5 text-4xl font-black uppercase text-white drop-shadow">${escapeHtml(section.title || 'Event Title')}</div>
                            </div>
                            ${isEditing(index) ? `
                                ${titleBlock(section, index, 'mt-4 text-2xl font-black text-slate-950')}
                                ${contentBlock(section, index, 'mt-3 leading-7 text-slate-600')}
                                <label class="mt-4 block">
                                    <span class="text-xs font-black uppercase text-slate-500">Image URL</span>
                                    <input data-setting-image="${index}" value="${escapeHtml(section.settings?.image_url || '')}" class="ds-input mt-2" placeholder="https://...">
                                </label>
                            ` : ''}
                        </div>
                    </section>`;
                }
                if (section.type === 'button_cta') {
                    return `<section data-row="${index}" data-editable-row class="${rowClasses(index)} text-center">
                        ${rowToolbar(index)}
                        <div class="p-4">
                            <div class="flex justify-end">${rowActions(index)}</div>
                            ${titleBlock(section, index, 'text-2xl font-black text-slate-950')}
                            ${contentBlock(section, index, 'mt-3 text-slate-600', 'Select a ticket and complete the form on this page.')}
                            <a href="${escapeHtml(section.settings?.button_url || '#tickets')}" class="mt-5 inline-flex rounded-full bg-slate-950 px-6 py-3 text-sm font-black text-white">${escapeHtml(section.settings?.button_label || 'View Tickets')}</a>
                            ${isEditing(index) ? `
                                <div class="mt-5 grid gap-3 text-left md:grid-cols-2">
                                    <label>
                                        <span class="text-xs font-black uppercase text-slate-500">Button Label</span>
                                        <input data-setting-button-label="${index}" value="${escapeHtml(section.settings?.button_label || '')}" class="ds-input mt-2" placeholder="View Tickets">
                                    </label>
                                    <label>
                                        <span class="text-xs font-black uppercase text-slate-500">Button Link</span>
                                        <input data-setting-button-url="${index}" value="${escapeHtml(section.settings?.button_url || '')}" class="ds-input mt-2" placeholder="#tickets">
                                    </label>
                                </div>
                            ` : ''}
                        </div>
                    </section>`;
                }
                if (section.type === 'footer') {
                    return `<footer data-row="${index}" data-editable-row class="${rowClasses(index)} text-center">
                        ${rowToolbar(index)}
                        <div class="p-4">
                            <div class="flex justify-end">${rowActions(index, true)}</div>
                            ${titleBlock(section, index, 'text-2xl font-black text-slate-950')}
                            ${contentBlock(section, index, 'mt-2 text-slate-500')}
                        </div>
                    </footer>`;
                }
                return `<section data-row="${index}" data-editable-row class="${rowClasses(index)}">
                    ${rowToolbar(index)}
                    <div class="p-4">
                        <div class="flex justify-end">${rowActions(index)}</div>
                        ${titleBlock(section, index, 'text-xl font-black uppercase text-slate-950')}
                        ${contentBlock(section, index, 'mt-2 leading-7 text-slate-500')}
                    </div>
                </section>`;
            };

            const rowActions = (index, locked = false) => {
                return `
                <div class="flex flex-wrap gap-2">
                    <button type="button" data-move-up="${index}" class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-700">Up</button>
                    <button type="button" data-move-down="${index}" class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-700">Down</button>
                    ${locked ? '' : `<button type="button" data-remove="${index}" class="rounded-full border border-red-200 bg-white px-3 py-1 text-xs font-bold text-red-700">Remove</button>`}
                </div>
            `};

            const sync = () => field.value = JSON.stringify(sections);
            const render = () => {
                ensureCore();
                canvas.innerHTML = `<div class="mx-auto max-w-6xl">${sections.map(sectionMarkup).join('')}</div>`;
                sync();
            };

            const addSection = (type = 'text_content') => {
                const footerIndex = sections.findIndex((section) => section.type === 'footer');
                const item = { type, title: type.replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase()), content: 'Double click to edit this section.', settings: {} };
                sections.splice(footerIndex >= 0 ? footerIndex : sections.length, 0, item);
                render();
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
                    syncCanvas();
                    sections.splice(Number(remove), 1);
                    render();
                }
                const up = event.target.closest('[data-move-up]')?.dataset.moveUp;
                if (up !== undefined && Number(up) > 0) {
                    syncCanvas();
                    [sections[Number(up) - 1], sections[Number(up)]] = [sections[Number(up)], sections[Number(up) - 1]];
                    render();
                }
                const down = event.target.closest('[data-move-down]')?.dataset.moveDown;
                if (down !== undefined && Number(down) < sections.length - 1) {
                    syncCanvas();
                    [sections[Number(down) + 1], sections[Number(down)]] = [sections[Number(down)], sections[Number(down) + 1]];
                    render();
                }
                const done = event.target.closest('[data-close-row-editor]')?.dataset.closeRowEditor;
                if (done !== undefined) {
                    syncCanvas();
                    editingIndex = null;
                    activeEditable = null;
                    render();
                    return;
                }

                const toolbarButton = event.target.closest('[data-row-toolbar] button');
                if (!toolbarButton) return;

                const row = toolbarButton.closest('[data-row]');
                const sourceEditor = row.querySelector('[data-html-source]');
                const command = toolbarButton.closest('[data-wysiwyg-command]')?.dataset.wysiwygCommand;
                const format = toolbarButton.closest('[data-wysiwyg-format]')?.dataset.wysiwygFormat;
                if (command || format || toolbarButton.closest('[data-wysiwyg-link]') || toolbarButton.closest('[data-wysiwyg-image]')) {
                    event.preventDefault();
                    activeEditable?.focus();
                }
                if (!activeEditable) return;
                if (command) document.execCommand(command, false);
                if (format) document.execCommand('formatBlock', false, format);
                if (toolbarButton.closest('[data-wysiwyg-link]')) {
                    const url = window.prompt('Enter link URL');
                    if (url) document.execCommand('createLink', false, url);
                }
                if (toolbarButton.closest('[data-wysiwyg-image]')) {
                    const url = window.prompt('Enter image URL');
                    if (url) document.execCommand('insertImage', false, url);
                }
                if (toolbarButton.closest('[data-wysiwyg-source]')) {
                    sourceEditor.classList.toggle('hidden');
                    sourceEditor.value = activeEditable.innerHTML;
                    sourceEditor.focus();
                }
                activeEditable.innerHTML = sanitizeHtml(activeEditable.innerHTML);
                syncRow(activeEditable.closest('[data-row]'));
            });

            root.addEventListener('focusin', (event) => {
                const editable = event.target.closest('[data-editable-title], [data-editable-content]');
                if (!editable) return;
                activeEditable = editable;
                const sourceEditor = editable.closest('[data-row]')?.querySelector('[data-html-source]');
                if (sourceEditor) {
                    sourceEditor.classList.add('hidden');
                    sourceEditor.value = editable.innerHTML;
                }
            });

            root.addEventListener('input', (event) => {
                const editable = event.target.closest('[data-editable-title], [data-editable-content]');
                const setting = event.target.closest('[data-setting-image], [data-setting-button-label], [data-setting-button-url]');
                const sourceEditor = event.target.closest('[data-html-source]');
                if (editable || setting) syncRow(event.target.closest('[data-row]'));
                if (sourceEditor && activeEditable) {
                    activeEditable.innerHTML = sanitizeHtml(sourceEditor.value);
                    syncRow(activeEditable.closest('[data-row]'));
                }
            });

            root.addEventListener('paste', (event) => {
                if (!event.target.closest('[data-editable-title], [data-editable-content]')) return;
                event.preventDefault();
                const html = event.clipboardData?.getData('text/html');
                const plain = event.clipboardData?.getData('text/plain') || '';
                document.execCommand('insertHTML', false, sanitizeHtml(html || escapeHtml(plain).replace(/\n/g, '<br>')));
                syncRow(event.target.closest('[data-row]'));
            });

            canvas.addEventListener('dblclick', (event) => {
                const row = event.target.closest('[data-editable-row]');
                if (!row) return;
                const index = Number(row.dataset.row);
                if (!isContentBlock(sections[index])) return;
                syncCanvas();
                editingIndex = index;
                render();
                window.setTimeout(() => {
                    const content = canvas.querySelector(`[data-editable-content="${index}"]`);
                    activeEditable = content;
                    content?.focus();
                }, 0);
            });

            root.addEventListener('submit', syncCanvas);
            render();
        })();
    </script>
</x-layouts.admin>
