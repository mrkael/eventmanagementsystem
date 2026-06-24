<x-layouts.admin title="Event Detail" heading="Event Detail" eyebrow="Foundation">
    <x-ui.page-header
        eyebrow="Event workspace"
        title="Annual Leadership Forum"
        description="A premium event detail foundation for future setup, microsite, registrations, communications, and attendance modules."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.foundation.edit') }}" class="ds-button-secondary">Edit shell</a>
            <a href="{{ route('core.events.foundation.microsite') }}" class="ds-button-primary"><x-ui.icon name="editor" class="size-4" /> Content editor</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">
        <div class="space-y-6">
            <div class="ds-card overflow-hidden">
                <div class="h-56 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-800"></div>
                <div class="p-6">
                    <div class="grid gap-4 md:grid-cols-4">
                        @foreach(['Microsite', 'Tickets', 'Forms', 'Sessions'] as $item)
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-sm font-bold text-slate-500">{{ $item }}</p>
                                <p class="mt-3 text-xl font-semibold text-slate-950">Ready</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <x-ui.card>
                <h2 class="text-xl font-semibold">Future module regions</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @foreach(['Registration form builder', 'Ticket configuration', 'Attendee management', 'Email campaigns'] as $region)
                        <div class="rounded-[20px] border border-slate-200 bg-white p-5">
                            <p class="font-bold">{{ $region }}</p>
                            <p class="mt-2 text-sm leading-6 text-slate-500">Region reserved for future workflow implementation.</p>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        </div>

        <x-ui.card class="h-max">
            <p class="text-sm font-bold text-slate-500">Event health shell</p>
            <div class="mt-5 space-y-4">
                <x-ui.skeleton class="h-4 w-3/4" />
                <x-ui.skeleton class="h-4 w-1/2" />
                <x-ui.skeleton class="h-28" />
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
