<x-layouts.admin title="Create Event" heading="Create Event" eyebrow="Foundation">
    <x-ui.page-header
        eyebrow="Event setup"
        title="Create event shell"
        description="A spacious creation layout prepared for future validation, autosave, and structured event setup."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.index') }}" class="ds-button-secondary">Back to events</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">
        <div class="space-y-6">
            <x-ui.card>
                <h2 class="text-xl font-semibold">Event identity</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label><span class="ds-label">Event name</span><input class="ds-input mt-2" placeholder="Annual Leadership Forum"></label>
                    <label><span class="ds-label">Custom site URL</span><input class="ds-input mt-2" placeholder="leadership-forum"></label>
                    <label class="md:col-span-2"><span class="ds-label">Description</span><textarea class="ds-input mt-2 min-h-32 py-3" placeholder="Short event description"></textarea></label>
                </div>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-xl font-semibold">Date, time, and location</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    <label><span class="ds-label">Start</span><input class="ds-input mt-2" placeholder="Date and time"></label>
                    <label><span class="ds-label">End</span><input class="ds-input mt-2" placeholder="Date and time"></label>
                    <label><span class="ds-label">Location</span><input class="ds-input mt-2" placeholder="Venue or online"></label>
                </div>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-xl font-semibold">Brand assets</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="rounded-[22px] border border-dashed border-slate-300 bg-slate-50 p-6"><x-ui.icon name="upload" /><p class="mt-4 font-bold">Logo upload</p></div>
                    <div class="rounded-[22px] border border-dashed border-slate-300 bg-slate-50 p-6"><x-ui.icon name="upload" /><p class="mt-4 font-bold">Banner upload</p></div>
                </div>
            </x-ui.card>
        </div>

        <x-ui.card class="h-max">
            <p class="text-sm font-bold text-slate-500">Creation summary</p>
            <div class="mt-5 space-y-3">
                <div class="rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-600">Identity</div>
                <div class="rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-600">Schedule</div>
                <div class="rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-600">Branding</div>
            </div>
            <div class="mt-6 rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-900">Persistence and validation will be connected after the UI foundation is approved.</div>
        </x-ui.card>
    </div>
</x-layouts.admin>
