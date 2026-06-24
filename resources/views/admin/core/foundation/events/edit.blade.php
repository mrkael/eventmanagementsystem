<x-layouts.admin title="Edit Event" heading="Edit Event" eyebrow="Foundation">
    <x-ui.page-header
        eyebrow="Edit shell"
        title="Edit event foundation"
        description="A refined edit surface prepared for future autosave, validation, audit states, and publishing controls."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.foundation.show') }}" class="ds-button-secondary">Back to detail</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">
        <div class="space-y-6">
            <x-ui.card>
                <h2 class="text-xl font-semibold">Core information</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label><span class="ds-label">Event name</span><input class="ds-input mt-2" value="Annual Leadership Forum"></label>
                    <label><span class="ds-label">Custom site URL</span><input class="ds-input mt-2" value="annual-leadership-forum"></label>
                    <label class="md:col-span-2"><span class="ds-label">Event summary</span><textarea class="ds-input mt-2 min-h-32 py-3">A flagship leadership experience for enterprise teams.</textarea></label>
                </div>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-xl font-semibold">Publishing foundation</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-sm font-bold text-slate-500">Status</p><p class="mt-2 font-semibold">Draft</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-sm font-bold text-slate-500">Microsite</p><p class="mt-2 font-semibold">Prepared</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-sm font-bold text-slate-500">Branding</p><p class="mt-2 font-semibold">Ready</p></div>
                </div>
            </x-ui.card>
        </div>

        <x-ui.card class="h-max">
            <p class="text-sm font-bold text-slate-500">Edit state panel</p>
            <div class="mt-5 space-y-3">
                <div class="rounded-2xl bg-amber-50 p-4 text-sm font-bold text-amber-900">Unsaved change state</div>
                <div class="rounded-2xl bg-emerald-50 p-4 text-sm font-bold text-emerald-900">Success state</div>
                <div class="rounded-2xl bg-red-50 p-4 text-sm font-bold text-red-900">Error state</div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
