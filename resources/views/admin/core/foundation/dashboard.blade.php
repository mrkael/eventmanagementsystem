<x-layouts.admin title="Dashboard" heading="Dashboard" eyebrow="Executive shell">
    <x-ui.page-header
        eyebrow="UI foundation"
        title="Good morning, {{ auth()->user()->name }}"
        description="A premium operating surface for event teams. The regions below establish layout, hierarchy, and interaction patterns before analytics and workflows are connected."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.create') }}" class="ds-button-primary"><x-ui.icon name="plus" class="size-4" /> New event shell</a>
            <a href="{{ route('core.organisers.index') }}" class="ds-button-secondary">Review profile</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 lg:grid-cols-4">
        @foreach(['Events ready', 'Draft regions', 'Editor surfaces', 'Brand assets'] as $label)
            <x-ui.card class="ds-card-hover">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold text-slate-500">{{ $label }}</p>
                    <span class="grid size-9 place-items-center rounded-2xl bg-blue-50 text-blue-700"><x-ui.icon name="spark" class="size-4" /></span>
                </div>
                <div class="mt-6 h-9 w-24 rounded-2xl bg-slate-950"></div>
                <p class="mt-4 text-sm leading-6 text-slate-500">Reserved KPI card pattern with loading and empty states.</p>
            </x-ui.card>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.15fr_.85fr]">
        <x-ui.card padding="p-0" class="overflow-hidden">
            <div class="border-b border-slate-200/80 p-6">
                <p class="text-sm font-bold text-slate-500">Quick actions</p>
                <h2 class="mt-1 text-xl font-semibold text-slate-950">Common starting points</h2>
            </div>
            <div class="grid divide-y divide-slate-100 md:grid-cols-3 md:divide-x md:divide-y-0">
                @foreach([
                    ['Create event', 'Design setup, branding, and content foundation.', 'calendar', route('core.events.create')],
                    ['Open content editor', 'Prepare microsite content blocks with Editor.js.', 'editor', route('core.events.foundation.microsite')],
                    ['Prepare email design', 'Shape future template editing experience.', 'mail', route('core.emails.index')],
                ] as [$title, $body, $icon, $href])
                    <a href="{{ $href }}" class="group p-6 transition hover:bg-slate-50">
                        <div class="grid size-11 place-items-center rounded-2xl bg-slate-950 text-white"><x-ui.icon :name="$icon" /></div>
                        <p class="mt-5 font-semibold text-slate-950">{{ $title }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">{{ $body }}</p>
                        <span class="mt-5 inline-flex items-center text-sm font-bold text-blue-700">Open <x-ui.icon name="arrow" class="ml-1 size-4 transition group-hover:translate-x-0.5" /></span>
                    </a>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.card>
            <p class="text-sm font-bold text-slate-500">Recent activity</p>
            <h2 class="mt-1 text-xl font-semibold text-slate-950">Activity region</h2>
            <div class="mt-6 space-y-4">
                @foreach([1,2,3] as $item)
                    <div class="flex gap-3">
                        <x-ui.skeleton class="size-10 shrink-0 rounded-full" />
                        <div class="flex-1 space-y-2">
                            <x-ui.skeleton class="h-3 w-3/4" />
                            <x-ui.skeleton class="h-3 w-1/2" />
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2">
            <p class="text-sm font-bold text-slate-500">Future dashboard region</p>
            <div class="mt-5 grid gap-3 md:grid-cols-3">
                <x-ui.skeleton class="h-32" />
                <x-ui.skeleton class="h-32" />
                <x-ui.skeleton class="h-32" />
            </div>
        </x-ui.card>
        <x-ui.empty-state icon="spark" title="No analytics yet" description="Business metrics will be connected after the UI foundation is approved." />
    </div>
</x-layouts.admin>
