<x-layouts.admin title="Microsite Editor" heading="Microsite Editor" eyebrow="Editor.js">
    <x-ui.page-header
        eyebrow="Content foundation"
        title="Event content editor"
        description="Editor.js is integrated as the foundation for future Hero Banner, Rich Content, Agenda, FAQ, Sponsors, Venue, and CTA blocks."
    />

    <div class="grid gap-6 xl:grid-cols-[18rem_1fr]">
        <x-ui.card>
            <p class="text-sm font-bold text-slate-500">Future blocks</p>
            <div class="mt-5 space-y-2">
                @foreach(['Hero Banner', 'Rich Content', 'Agenda', 'FAQ', 'Sponsors', 'Venue', 'CTA'] as $block)
                    <div class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600">{{ $block }}</div>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-500">Editor canvas</p>
                    <h2 class="mt-1 text-xl font-semibold">Microsite content</h2>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">Foundation only</span>
            </div>
            <div id="event-content-editor" data-editorjs='{"holder":"event-content-editor","placeholder":"Start shaping the event page content foundation...","data":{"blocks":[{"type":"paragraph","data":{"text":"Use this surface to prepare future event page sections."}}]}}' class="min-h-96 rounded-[22px] border border-slate-200 bg-white p-5"></div>
        </x-ui.card>
    </div>
</x-layouts.admin>
