<x-layouts.admin title="Microsite CMS" heading="Microsite CMS" subheading="{{ $event->title }}">
    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('core.events.show', $event) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">Back to event</a>
        @if($event->custom_url)
            <a target="_blank" href="{{ route('core.public.events.show', $event) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">Preview public page</a>
        @endif
        <form method="POST" action="{{ route('core.events.microsite.publish', $event) }}">@csrf<button class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white">Publish microsite</button></form>
    </div>
    <form method="POST" action="{{ route('core.events.microsite.update', $event) }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm" data-builder>
        @csrf @method('PUT')
        <input type="hidden" name="template" value="default">
        <input type="hidden" name="sections" data-sections-json value="{{ old('sections', $page->sections->map(fn($section) => ['type' => $section->type, 'title' => $section->title, 'content' => $section->content, 'settings' => $section->settings])->values()->toJson()) }}">
        <div class="mb-4 flex flex-wrap gap-2">
            @foreach($sectionTypes as $type => $label)
                <button type="button" data-add-section="{{ $type }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold">Add {{ $label }}</button>
            @endforeach
        </div>
        <div data-sections class="space-y-3"></div>
        <div class="mt-5 flex justify-end"><button class="min-h-11 rounded-lg bg-emerald-700 px-5 text-sm font-semibold text-white">Save draft</button></div>
    </form>
    <script>
        const labels = @json($sectionTypes);
        const root = document.querySelector('[data-builder]');
        const holder = root.querySelector('[data-sections]');
        const field = root.querySelector('[data-sections-json]');
        let sections = JSON.parse(field.value || '[]');
        const sync = () => field.value = JSON.stringify(sections);
        const render = () => {
            holder.innerHTML = '';
            sections.forEach((section, index) => {
                const item = document.createElement('div');
                item.className = 'rounded-lg border border-slate-200 p-4';
                item.innerHTML = `<div class="flex flex-wrap items-center gap-2"><strong>${labels[section.type] || section.type}</strong><button type="button" data-up="${index}" class="ml-auto rounded border px-2 py-1 text-xs">Up</button><button type="button" data-down="${index}" class="rounded border px-2 py-1 text-xs">Down</button><button type="button" data-copy="${index}" class="rounded border px-2 py-1 text-xs">Duplicate</button><button type="button" data-remove="${index}" class="rounded border px-2 py-1 text-xs text-red-700">Remove</button></div><label class="mt-3 block text-sm font-medium">Title<input class="mt-1 w-full rounded-lg border-slate-300" data-title="${index}" value="${section.title || ''}"></label><label class="mt-3 block text-sm font-medium">Content<textarea rows="4" class="mt-1 w-full rounded-lg border-slate-300" data-content="${index}">${section.content || ''}</textarea></label>`;
                holder.appendChild(item);
            });
            sync();
        };
        root.addEventListener('input', event => {
            const title = event.target.dataset.title;
            const content = event.target.dataset.content;
            if (title !== undefined) sections[title].title = event.target.value;
            if (content !== undefined) sections[content].content = event.target.value;
            sync();
        });
        root.addEventListener('click', event => {
            const add = event.target.dataset.addSection;
            if (add) sections.push({type: add, title: labels[add], content: '', settings: {}});
            const remove = event.target.dataset.remove;
            if (remove !== undefined) sections.splice(remove, 1);
            const copy = event.target.dataset.copy;
            if (copy !== undefined) sections.splice(Number(copy) + 1, 0, {...sections[copy]});
            const up = event.target.dataset.up;
            if (up > 0) [sections[up - 1], sections[up]] = [sections[up], sections[up - 1]];
            const down = event.target.dataset.down;
            if (down !== undefined && Number(down) < sections.length - 1) [sections[Number(down) + 1], sections[down]] = [sections[down], sections[Number(down) + 1]];
            render();
        });
        render();
    </script>
</x-layouts.admin>
