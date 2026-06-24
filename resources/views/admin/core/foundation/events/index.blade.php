<x-layouts.admin title="Events" heading="Events" eyebrow="Core module">
    <x-ui.page-header
        eyebrow="Search first"
        title="Events command center"
        description="A clean event workspace foundation with modern filters, card/table patterns, loading states, and empty states."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.create') }}" class="ds-button-primary"><x-ui.icon name="plus" class="size-4" /> Create event</a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card>
        <div class="grid gap-3 lg:grid-cols-[1fr_auto_auto]">
            <label class="relative">
                <x-ui.icon name="search" class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input class="ds-input pl-11" placeholder="Search events, organiser, city, status">
            </label>
            <button type="button" class="ds-button-secondary"><x-ui.icon name="filter" class="size-4" /> Filters</button>
            <div class="flex rounded-full border border-slate-200 bg-slate-50 p-1">
                <button type="button" class="rounded-full bg-white px-4 py-2 text-sm font-bold text-slate-950 shadow-sm">Cards</button>
                <button type="button" class="rounded-full px-4 py-2 text-sm font-bold text-slate-500">Table</button>
            </div>
        </div>
    </x-ui.card>

    <div class="mt-6 grid gap-5 xl:grid-cols-3">
        @foreach([
            ['Annual Leadership Forum', 'Kuala Lumpur Convention Centre', 'Draft foundation', 'May 2027'],
            ['Product Summit Asia', 'Singapore', 'Microsite planned', 'June 2027'],
            ['Customer Experience Week', 'Jakarta', 'Brand setup', 'July 2027'],
        ] as [$name, $place, $status, $date])
            <a href="{{ route('core.events.foundation.show') }}" class="ds-card ds-card-hover block overflow-hidden">
                <div class="h-32 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-800"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase text-blue-600">{{ $date }}</p>
                            <h2 class="mt-2 text-lg font-semibold text-slate-950">{{ $name }}</h2>
                        </div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">{{ $status }}</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">{{ $place }}</p>
                    <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 text-sm font-bold text-slate-500">
                        <span>Foundation preview</span>
                        <x-ui.icon name="arrow" class="size-4" />
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-2">
        <x-ui.card>
            <p class="text-sm font-bold text-slate-500">Loading state</p>
            <div class="mt-5 space-y-3">
                <x-ui.skeleton class="h-5 w-2/3" />
                <x-ui.skeleton class="h-5 w-1/2" />
                <x-ui.skeleton class="h-24" />
            </div>
        </x-ui.card>
        <x-ui.empty-state icon="calendar" title="Empty event state" description="When no events exist, this region introduces the product and guides users to create their first event." />
    </div>
</x-layouts.admin>
