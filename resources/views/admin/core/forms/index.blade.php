<x-layouts.admin title="Registration Forms" heading="Registration Forms" subheading="{{ $event->title }}">
    <form method="POST" action="{{ route('core.events.forms.store', $event) }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        <div class="grid gap-4 lg:grid-cols-2">
            <label class="block"><span class="text-sm font-medium">Form name</span><input name="title" required class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Description</span><input name="description" class="mt-1 w-full rounded-lg border-slate-300"></label>
        </div>
        <div class="mt-5" data-fields>
            <div class="mb-2 flex items-center justify-between"><h2 class="font-semibold">Custom questions</h2><button type="button" data-add-field class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold">Add question</button></div>
            <div data-field-list class="space-y-3"></div>
        </div>
        <div class="mt-5 flex justify-end"><button class="min-h-11 rounded-lg bg-emerald-700 px-5 text-sm font-semibold text-white">Save form</button></div>
    </form>
    <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b px-5 py-4"><h2 class="font-semibold">Existing forms</h2></div>
        <div class="divide-y divide-slate-100">
            @forelse($forms as $form)
                <div class="px-5 py-4 text-sm"><p class="font-semibold">{{ $form->title }}</p><p class="text-slate-500">{{ $form->fields_count }} custom questions, {{ $form->tickets_count }} linked tickets</p></div>
            @empty
                <p class="px-5 py-6 text-slate-500">No forms yet.</p>
            @endforelse
        </div>
    </section>
    <script>
        const types = @json($fieldTypes);
        const list = document.querySelector('[data-field-list]');
        document.querySelector('[data-add-field]').addEventListener('click', () => {
            const i = list.children.length;
            const row = document.createElement('div');
            row.className = 'grid gap-3 rounded-lg border border-slate-200 p-3 md:grid-cols-5';
            row.innerHTML = `<input name="fields[${i}][label]" placeholder="Label" required class="rounded-lg border-slate-300"><select name="fields[${i}][type]" class="rounded-lg border-slate-300">${types.map(t => `<option value="${t}">${t}</option>`).join('')}</select><input name="fields[${i}][placeholder]" placeholder="Placeholder" class="rounded-lg border-slate-300"><textarea name="fields[${i}][options]" placeholder="Options, one per line" class="rounded-lg border-slate-300"></textarea><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="fields[${i}][is_required]" value="1"> Required</label>`;
            list.appendChild(row);
        });
    </script>
</x-layouts.admin>
