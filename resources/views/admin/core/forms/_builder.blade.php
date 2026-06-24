@php
    $initialFields = old('fields_payload') ? json_decode(old('fields_payload'), true) : $builderFields;
    $initialCustomQuestions = old('custom_questions_payload') ? json_decode(old('custom_questions_payload'), true) : [];
    $selectedTickets = collect(old('ticket_ids', $form->exists ? $form->tickets->pluck('id')->all() : []))->map(fn ($id) => (string) $id)->all();
    $customLibrary = $customQuestions->map(fn ($question) => [
        'source_type' => 'custom',
        'field_key' => 'custom_'.$question->id,
        'label' => $question->question_name,
        'type' => $question->type,
        'placeholder' => $question->placeholder,
        'error_text' => $question->error_text,
        'is_required' => false,
        'options' => $question->options ?: [],
    ])->values()->all();
@endphp

<input type="hidden" name="fields_payload" data-fields-payload value="{{ old('fields_payload') }}">
<input type="hidden" name="custom_questions_payload" data-custom-questions-payload value="{{ old('custom_questions_payload') }}">

<div
    data-builder-root
    data-basic-fields='@json($basicFields)'
    data-custom-fields='@json($customLibrary)'
    data-initial-fields='@json($initialFields ?: [])'
    data-initial-custom-questions='@json($initialCustomQuestions ?: [])'
    data-field-types='@json($fieldTypes)'
>
    <x-ui.card class="mb-6">
        <div class="grid gap-4 lg:grid-cols-2">
            <label class="block">
                <span class="ds-label">Form Title</span>
                <input name="title" value="{{ old('title', $form?->title) }}" required class="ds-input mt-2" placeholder="Registration Form">
                @error('title')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
            </label>
            <label class="form-ticket-select block">
                <span class="ds-label">Assigned Ticket</span>
                <select name="ticket_ids[]" multiple required data-ticket-select class="mt-2">
                    @foreach($tickets as $ticket)
                        <option value="{{ $ticket->id }}" @selected(in_array((string) $ticket->id, $selectedTickets, true))>{{ $ticket->name }}</option>
                    @endforeach
                </select>
                <span class="mt-2 block text-xs font-semibold text-slate-500">Search and select one or more tickets for this form.</span>
                @error('ticket_ids')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                @if($tickets->isEmpty())
                    <span class="mt-2 block text-sm font-semibold text-amber-700">Create at least one ticket before building a registration form.</span>
                @endif
            </label>
        </div>
    </x-ui.card>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="min-w-0">
            <x-ui.card>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-950">Form Canvas</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Drag fields from the right panel into this canvas. Reorder fields by dragging them inside the canvas.</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><span data-field-count>0</span> fields</span>
                </div>
                <div data-canvas class="mt-6 min-h-96 space-y-3 rounded-[24px] border border-dashed border-slate-300 bg-slate-50/70 p-4"></div>
                @error('fields_payload')<span class="mt-3 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
            </x-ui.card>
        </div>

        <aside class="space-y-6 xl:sticky xl:top-24 xl:self-start">
            <x-ui.card>
                <h2 class="text-xl font-semibold text-slate-950">Basic Fields</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Drag or click to add reusable basic fields.</p>
                <div data-basic-library class="mt-5 space-y-2"></div>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-xl font-semibold text-slate-950">Custom Questions</h2>
                    <button type="button" data-open-custom-question class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Create New</button>
                </div>
                <p class="mt-2 text-sm leading-6 text-slate-500">Create event-specific questions, then drag them into the canvas.</p>
                <div data-custom-library class="mt-5 space-y-2"></div>
                <div data-empty-custom class="mt-5 rounded-[22px] border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500">No custom questions yet.</div>
            </x-ui.card>

            <x-ui.card>
                <div class="space-y-3">
                    <button type="submit" class="ds-button-primary w-full justify-center" @disabled($tickets->isEmpty())>Save Form</button>
                    <a href="{{ route('core.events.forms.index', $event) }}" class="ds-button-secondary w-full justify-center">Cancel</a>
                </div>
            </x-ui.card>
        </aside>
    </div>

    <div data-custom-question-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/45 p-4">
        <div class="w-full max-w-lg rounded-[28px] border border-white/60 bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950">Create custom question</h2>
                    <p class="mt-1 text-sm text-slate-500">Save it into this event library, then drag it into the form canvas.</p>
                </div>
                <button type="button" data-close-custom-question class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600">Close</button>
            </div>
            <div class="mt-5 space-y-4">
                <label class="block">
                    <span class="ds-label">Question Name</span>
                    <input data-custom-name class="ds-input mt-2" placeholder="Question name">
                </label>
                <label class="block">
                    <span class="ds-label">Type</span>
                    <select data-custom-type class="ds-input mt-2">
                        @foreach($fieldTypes as $type)
                            <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="ds-label">Placeholder</span>
                    <input data-custom-placeholder class="ds-input mt-2" placeholder="Placeholder">
                </label>
                <label class="block">
                    <span class="ds-label">Error Text</span>
                    <input data-custom-error class="ds-input mt-2" placeholder="Error text">
                </label>
                <label data-custom-options-wrap class="hidden">
                    <span class="ds-label">Options</span>
                    <textarea data-custom-options rows="4" class="ds-input mt-2 py-3" placeholder="One option per line"></textarea>
                </label>
                <p data-custom-modal-error class="hidden text-sm font-semibold text-red-700"></p>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" data-close-custom-question class="ds-button-secondary">Cancel</button>
                <button type="button" data-save-custom-question class="ds-button-primary">Save Question</button>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const boot = () => {
        const root = document.querySelector('[data-builder-root]');
        if (!root) return;

        if (!window.Sortable || !window.TomSelect) {
            window.setTimeout(boot, 50);
            return;
        }

        const basicFields = JSON.parse(root.dataset.basicFields || '[]');
        const fieldTypes = JSON.parse(root.dataset.fieldTypes || '[]');
        let customFields = JSON.parse(root.dataset.customFields || '[]');
        let fields = JSON.parse(root.dataset.initialFields || '[]').map((field) => ({ ...field, _expanded: false }));
        let newCustomQuestions = JSON.parse(root.dataset.initialCustomQuestions || '[]');

        const canvas = root.querySelector('[data-canvas]');
        const basicLibrary = root.querySelector('[data-basic-library]');
        const customLibrary = root.querySelector('[data-custom-library]');
        const emptyCustom = root.querySelector('[data-empty-custom]');
        const count = root.querySelector('[data-field-count]');
        const fieldsPayload = root.parentElement.querySelector('[data-fields-payload]');
        const customQuestionsPayload = root.parentElement.querySelector('[data-custom-questions-payload]');
        const modal = root.querySelector('[data-custom-question-modal]');
        const modalError = root.querySelector('[data-custom-modal-error]');
        const customType = root.querySelector('[data-custom-type]');
        const customOptionsWrap = root.querySelector('[data-custom-options-wrap]');
        const optionTypes = ['dropdown', 'radio', 'checkbox'];

        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
        const optionText = (options) => (options || []).join('\n');
        const cleanOptions = (value) => String(value || '').split(/\r?\n/).map((option) => option.trim()).filter(Boolean);
        const stripState = (field) => {
            const { _expanded, ...clean } = field;
            return clean;
        };
        const uniqueInstanceKey = (field) => `${field.field_key || field.source_type || 'field'}_${Date.now()}_${Math.floor(Math.random() * 10000)}`;

        const syncPayload = () => {
            fieldsPayload.value = JSON.stringify(fields.map(stripState));
            customQuestionsPayload.value = JSON.stringify(newCustomQuestions);
            count.textContent = fields.length;
        };

        const templateData = (field) => ({
            source_type: field.source_type || 'custom',
            field_key: uniqueInstanceKey(field),
            label: field.label || field.question_name || 'Untitled question',
            type: field.type || 'text',
            placeholder: field.placeholder || '',
            error_text: field.error_text || '',
            is_required: Boolean(field.is_required),
            options: field.options || [],
            _expanded: true,
        });

        const addField = (field, index = fields.length) => {
            fields.splice(index, 0, templateData(field));
            renderCanvas();
        };

        const libraryItem = (field) => {
            const item = document.createElement('div');
            item.dataset.template = JSON.stringify(field);
            item.className = 'flex w-full cursor-grab items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-bold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700';
            item.innerHTML = `<span>${escapeHtml(field.label || field.question_name)}</span><span class="text-xs uppercase text-slate-400">${escapeHtml(field.type)}</span>`;
            item.addEventListener('click', () => addField(field));
            return item;
        };

        const renderLibraries = () => {
            basicLibrary.innerHTML = '';
            customLibrary.innerHTML = '';
            basicFields.forEach((field) => basicLibrary.appendChild(libraryItem(field)));
            customFields.forEach((field) => customLibrary.appendChild(libraryItem(field)));
            emptyCustom.classList.toggle('hidden', customFields.length > 0);
            initLibrarySortables();
        };

        const renderCanvas = () => {
            canvas.innerHTML = '';
            if (fields.length === 0) {
                const empty = document.createElement('div');
                empty.dataset.emptyCanvas = 'true';
                empty.className = 'rounded-[24px] border border-dashed border-slate-300 bg-white/70 p-10 text-center';
                empty.innerHTML = `
                    <div class="mx-auto grid size-12 place-items-center rounded-2xl bg-blue-50 text-blue-700">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5.25 18.75h13.5M7.25 15.25l7.83-7.83a2 2 0 0 1 2.83 2.83l-7.83 7.83-3.83.67z" /></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-slate-950">Drop fields here</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">Basic fields and custom questions stay reusable in the right panel.</p>
                `;
                canvas.appendChild(empty);
            }
            fields.forEach((field, index) => {
                const item = document.createElement('section');
                item.dataset.index = index;
                item.className = 'rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm';
                item.innerHTML = `
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <button type="button" data-drag-handle class="w-max cursor-grab rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-500">Drag</button>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-950">${escapeHtml(field.label)} ${field.is_required ? '<span class="text-red-600">*</span>' : ''}</p>
                            <p class="mt-1 text-xs font-semibold uppercase text-slate-400">${escapeHtml(field.type)}</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" data-toggle-field="${index}" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700">${field._expanded ? 'Collapse' : 'Expand'}</button>
                            <button type="button" data-remove-field="${index}" class="rounded-full border border-red-200 px-3 py-2 text-xs font-bold text-red-700">Remove</button>
                        </div>
                    </div>
                    <div class="${field._expanded ? '' : 'hidden'} mt-5 grid gap-4 md:grid-cols-2">
                        <label><span class="ds-label">Question Name</span><input data-field-prop="label" data-field-index="${index}" class="ds-input mt-2" value="${escapeHtml(field.label)}"></label>
                        <label><span class="ds-label">Field Type</span><select data-field-prop="type" data-field-index="${index}" class="ds-input mt-2">${fieldTypes.map((type) => `<option value="${escapeHtml(type)}" ${field.type === type ? 'selected' : ''}>${escapeHtml(type)}</option>`).join('')}</select></label>
                        <label><span class="ds-label">Placeholder</span><input data-field-prop="placeholder" data-field-index="${index}" class="ds-input mt-2" value="${escapeHtml(field.placeholder)}"></label>
                        <label><span class="ds-label">Error Text</span><input data-field-prop="error_text" data-field-index="${index}" class="ds-input mt-2" value="${escapeHtml(field.error_text)}"></label>
                        <label class="flex min-h-11 items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700"><input type="checkbox" data-field-prop="is_required" data-field-index="${index}" ${field.is_required ? 'checked' : ''} class="rounded border-slate-300 text-blue-700 focus:ring-blue-600"> Required field</label>
                        ${optionTypes.includes(field.type) ? `<label class="md:col-span-2"><span class="ds-label">Options</span><textarea data-field-prop="options" data-field-index="${index}" rows="4" class="ds-input mt-2 py-3">${escapeHtml(optionText(field.options))}</textarea></label>` : ''}
                    </div>
                `;
                canvas.appendChild(item);
            });
            syncPayload();
        };

        const initLibrarySortables = () => {
            [basicLibrary, customLibrary].forEach((library) => {
                if (library.sortableInstance) {
                    library.sortableInstance.destroy();
                }
                library.sortableInstance = new window.Sortable(library, {
                    group: { name: 'form-fields', pull: 'clone', put: false },
                    sort: false,
                    animation: 150,
                    ghostClass: 'opacity-40',
                });
            });
        };

        new window.Sortable(canvas, {
            group: { name: 'form-fields', pull: true, put: true },
            handle: '[data-drag-handle]',
            animation: 150,
            ghostClass: 'opacity-40',
            onAdd: (event) => {
                const template = event.item.dataset.template ? JSON.parse(event.item.dataset.template) : null;
                event.item.remove();
                if (template) {
                    addField(template, fields.length === 0 ? 0 : Math.min(event.newIndex, fields.length));
                }
            },
            onUpdate: () => {
                fields = Array.from(canvas.children).filter((item) => !item.dataset.emptyCanvas).map((item) => fields[Number(item.dataset.index)]).filter(Boolean);
                renderCanvas();
            },
        });

        root.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-toggle-field]');
            const remove = event.target.closest('[data-remove-field]');
            if (toggle) {
                fields[Number(toggle.dataset.toggleField)]._expanded = !fields[Number(toggle.dataset.toggleField)]._expanded;
                renderCanvas();
            }
            if (remove) {
                fields.splice(Number(remove.dataset.removeField), 1);
                renderCanvas();
            }
        });

        root.addEventListener('input', (event) => {
            const input = event.target.closest('[data-field-prop]');
            if (!input) return;
            const index = Number(input.dataset.fieldIndex);
            const prop = input.dataset.fieldProp;
            fields[index][prop] = prop === 'options' ? cleanOptions(input.value) : input.value;
            syncPayload();
        });

        root.addEventListener('change', (event) => {
            const input = event.target.closest('[data-field-prop]');
            if (!input) return;
            const index = Number(input.dataset.fieldIndex);
            const prop = input.dataset.fieldProp;
            fields[index][prop] = prop === 'is_required' ? input.checked : input.value;
            renderCanvas();
        });

        const resetModal = () => {
            modalError.classList.add('hidden');
            root.querySelector('[data-custom-name]').value = '';
            root.querySelector('[data-custom-placeholder]').value = '';
            root.querySelector('[data-custom-error]').value = '';
            root.querySelector('[data-custom-options]').value = '';
            customType.value = 'text';
            customOptionsWrap.classList.add('hidden');
        };

        root.querySelectorAll('[data-open-custom-question]').forEach((button) => button.addEventListener('click', () => {
            resetModal();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }));

        root.querySelectorAll('[data-close-custom-question]').forEach((button) => button.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }));

        customType.addEventListener('change', () => {
            customOptionsWrap.classList.toggle('hidden', !optionTypes.includes(customType.value));
        });

        root.querySelector('[data-save-custom-question]').addEventListener('click', () => {
            const name = root.querySelector('[data-custom-name]').value.trim();
            const type = customType.value;
            const options = cleanOptions(root.querySelector('[data-custom-options]').value);
            if (!name || (optionTypes.includes(type) && options.length === 0)) {
                modalError.textContent = !name ? 'Question name is required.' : 'Options are required for this question type.';
                modalError.classList.remove('hidden');
                return;
            }
            const question = {
                source_type: 'custom',
                field_key: `custom_new_${Date.now()}`,
                question_name: name,
                label: name,
                type,
                placeholder: root.querySelector('[data-custom-placeholder]').value,
                error_text: root.querySelector('[data-custom-error]').value,
                options,
                is_required: false,
            };
            customFields.push(question);
            newCustomQuestions.push(question);
            renderLibraries();
            syncPayload();
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });

        new window.TomSelect(root.querySelector('[data-ticket-select]'), {
            plugins: ['remove_button'],
            maxItems: null,
            create: false,
            hideSelected: true,
            closeAfterSelect: false,
            placeholder: 'Search and select ticket',
        });

        renderLibraries();
        renderCanvas();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot);
        } else {
            boot();
        }
    })();
</script>
