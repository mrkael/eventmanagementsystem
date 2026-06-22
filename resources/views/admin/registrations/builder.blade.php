<x-layouts.admin title="Registration Builder" heading="Registration Builder" subheading="{{ $event->title }}">
    <form method="POST" action="{{ route('admin.events.registrations.builder.update', $event) }}" class="grid gap-6 xl:grid-cols-[1fr_340px]">
        @csrf
        @method('PUT')
        <input type="hidden" name="schema" data-schema value="{{ e(json_encode(old('schema') ? json_decode(old('schema'), true) : $schema)) }}">

        <section class="space-y-4">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold">Questions</h2>
                        <p class="text-sm text-slate-500">Group questions by step and keep labels short for mobile registration.</p>
                    </div>
                    <button type="button" data-add-group class="min-h-11 rounded-lg border border-slate-300 px-4 text-sm font-semibold hover:bg-slate-50">Add group</button>
                </div>
                <div data-groups class="mt-5 space-y-4"></div>
            </div>
        </section>

        <aside class="space-y-4">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Form settings</h2>
                <div class="mt-4 space-y-4">
                    <label class="block"><span class="text-sm font-medium">Title</span><input name="title" value="{{ old('title', $form?->title ?? $event->title.' Registration') }}" required class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm"></label>
                    <label class="block"><span class="text-sm font-medium">Description</span><textarea name="description" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('description', $form?->description) }}</textarea></label>
                    <label class="block"><span class="text-sm font-medium">Access mode</span><select name="access_mode" class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm"><option value="public" @selected(old('access_mode', $form?->access_mode ?? 'public')==='public')>Public</option><option value="private" @selected(old('access_mode', $form?->access_mode)==='private')>Private</option><option value="invite" @selected(old('access_mode', $form?->access_mode)==='invite')>Invite only</option></select></label>
                    <div class="grid gap-2 text-sm">
                        <label class="flex items-center gap-2"><input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $form?->is_enabled ?? true))> Enabled</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="requires_approval" value="1" @checked(old('requires_approval', $form?->requires_approval ?? false))> Requires approval</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="allow_waitlist" value="1" @checked(old('allow_waitlist', $form?->allow_waitlist ?? true))> Allow waitlist</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="is_multi_step" value="1" @checked(old('is_multi_step', $form?->is_multi_step ?? false))> Multi-step form</label>
                    </div>
                    <label class="block"><span class="text-sm font-medium">Opens at</span><input type="datetime-local" name="opens_at" value="{{ old('opens_at', $form?->opens_at?->format('Y-m-d\TH:i')) }}" class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm"></label>
                    <label class="block"><span class="text-sm font-medium">Closes at</span><input type="datetime-local" name="closes_at" value="{{ old('closes_at', $form?->closes_at?->format('Y-m-d\TH:i')) }}" class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm"></label>
                    <button class="min-h-11 w-full rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800">Save builder</button>
                    <a href="{{ route('admin.events.registrations.index', $event) }}" class="flex min-h-11 items-center justify-center rounded-lg border border-slate-300 text-sm font-semibold hover:bg-slate-50">Manage participants</a>
                </div>
            </div>
        </aside>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const hidden = document.querySelector('[data-schema]');
            const groupsRoot = document.querySelector('[data-groups]');
            const types = ['text','textarea','email','number','date','dropdown','radio','checkbox','file'];
            let schema = JSON.parse(hidden.value || '{"groups":[]}');

            const sync = () => hidden.value = JSON.stringify(schema);
            const render = () => {
                groupsRoot.innerHTML = '';
                schema.groups.forEach((group, groupIndex) => {
                    const article = document.createElement('article');
                    article.className = 'rounded-lg border border-slate-200 p-4';
                    article.innerHTML = `
                        <div class="grid gap-3 md:grid-cols-[1fr_130px_auto]">
                            <input class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm" value="${group.title || ''}" data-group-title="${groupIndex}" placeholder="Group title">
                            <input type="number" min="1" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm" value="${group.step_number || groupIndex + 1}" data-group-step="${groupIndex}" aria-label="Step number">
                            <button type="button" class="min-h-11 rounded-lg border border-red-200 px-3 text-sm font-semibold text-red-700" data-remove-group="${groupIndex}">Remove</button>
                        </div>
                        <textarea class="mt-3 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" data-group-description="${groupIndex}" rows="2" placeholder="Group description">${group.description || ''}</textarea>
                        <div class="mt-4 space-y-3" data-question-list="${groupIndex}"></div>
                        <button type="button" class="mt-3 min-h-11 rounded-lg border border-slate-300 px-4 text-sm font-semibold hover:bg-slate-50" data-add-question="${groupIndex}">Add question</button>
                    `;
                    groupsRoot.appendChild(article);
                    const list = article.querySelector(`[data-question-list="${groupIndex}"]`);
                    (group.questions || []).forEach((question, questionIndex) => {
                        const row = document.createElement('div');
                        row.className = 'rounded-lg bg-slate-50 p-3';
                        row.innerHTML = `
                            <div class="grid gap-3 lg:grid-cols-[140px_1fr_170px_auto_auto]">
                                <select class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm" data-question-type="${groupIndex}:${questionIndex}">${types.map(type => `<option value="${type}" ${question.type === type ? 'selected' : ''}>${type}</option>`).join('')}</select>
                                <input class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm" value="${question.label || ''}" data-question-label="${groupIndex}:${questionIndex}" placeholder="Question label">
                                <input class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm" value="${question.key || ''}" data-question-key="${groupIndex}:${questionIndex}" placeholder="field_key">
                                <label class="flex min-h-11 items-center gap-2 text-sm"><input type="checkbox" data-question-required="${groupIndex}:${questionIndex}" ${question.is_required ? 'checked' : ''}> Required</label>
                                <button type="button" class="min-h-11 rounded-lg border border-red-200 px-3 text-sm font-semibold text-red-700" data-remove-question="${groupIndex}:${questionIndex}">Remove</button>
                            </div>
                            <textarea class="mt-3 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" rows="2" data-question-options="${groupIndex}:${questionIndex}" placeholder="Options, one per line">${(question.options || []).join('\n')}</textarea>
                            <div class="mt-3 grid gap-3 md:grid-cols-2">
                                <textarea class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" rows="2" data-question-rules="${groupIndex}:${questionIndex}" placeholder='Validation JSON, e.g. ["max:255"]'>${JSON.stringify(question.validation_rules || [])}</textarea>
                                <textarea class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" rows="2" data-question-logic="${groupIndex}:${questionIndex}" placeholder='Conditional JSON'>${JSON.stringify(question.conditional_logic || [])}</textarea>
                            </div>
                        `;
                        list.appendChild(row);
                    });
                });
                sync();
            };
            const parseJson = (value) => { try { return JSON.parse(value || '[]') || []; } catch { return []; } };
            document.addEventListener('input', (event) => {
                const attr = [...event.target.attributes].find(attribute => attribute.name.startsWith('data-group-') || attribute.name.startsWith('data-question-'));
                if (!attr) return;
                const [groupIndex, questionIndex] = String(attr.value).split(':').map(Number);
                if (attr.name === 'data-group-title') schema.groups[groupIndex].title = event.target.value;
                if (attr.name === 'data-group-description') schema.groups[groupIndex].description = event.target.value;
                if (attr.name === 'data-group-step') schema.groups[groupIndex].step_number = Number(event.target.value || 1);
                if (attr.name === 'data-question-type') schema.groups[groupIndex].questions[questionIndex].type = event.target.value;
                if (attr.name === 'data-question-label') schema.groups[groupIndex].questions[questionIndex].label = event.target.value;
                if (attr.name === 'data-question-key') schema.groups[groupIndex].questions[questionIndex].key = event.target.value;
                if (attr.name === 'data-question-required') schema.groups[groupIndex].questions[questionIndex].is_required = event.target.checked;
                if (attr.name === 'data-question-options') schema.groups[groupIndex].questions[questionIndex].options = event.target.value.split(/\r?\n/).filter(Boolean);
                if (attr.name === 'data-question-rules') schema.groups[groupIndex].questions[questionIndex].validation_rules = parseJson(event.target.value);
                if (attr.name === 'data-question-logic') schema.groups[groupIndex].questions[questionIndex].conditional_logic = parseJson(event.target.value);
                sync();
            });
            document.addEventListener('click', (event) => {
                const button = event.target.closest('button');
                if (!button) return;
                if (button.dataset.addGroup !== undefined) schema.groups.push({title:'New Group', description:'', step_number:schema.groups.length + 1, questions:[]});
                if (button.dataset.addQuestion !== undefined) schema.groups[Number(button.dataset.addQuestion)].questions.push({type:'text', label:'New question', key:'new_question', is_required:false, options:[], validation_rules:[], conditional_logic:[]});
                if (button.dataset.removeGroup !== undefined) schema.groups.splice(Number(button.dataset.removeGroup), 1);
                if (button.dataset.removeQuestion !== undefined) { const [groupIndex, questionIndex] = button.dataset.removeQuestion.split(':').map(Number); schema.groups[groupIndex].questions.splice(questionIndex, 1); }
                render();
            });
            render();
        });
    </script>
</x-layouts.admin>
