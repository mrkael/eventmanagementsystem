<x-layouts.admin title="Events" heading="Events" eyebrow="Core module">
    <x-ui.page-header
        eyebrow="Event workspace"
        title="Events"
        description="Manage events by organiser profile, configure their settings, and prepare tickets for registration."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.create') }}" class="ds-button-primary"><x-ui.icon name="plus" class="size-4" /> Create Event</a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card>
        <form method="GET" class="grid gap-3 xl:grid-cols-[1fr_auto_auto_auto]">
            <label class="relative">
                <x-ui.icon name="search" class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input name="search" value="{{ request('search') }}" class="ds-input pl-11" placeholder="Search by event name">
            </label>
            @if($isPlatformAdmin ?? false)
                <select name="organiser_profile_id" class="ds-input xl:w-64">
                    <option value="">All organisers</option>
                    @foreach($organiserProfiles as $profile)
                        <option value="{{ $profile->id }}" @selected((string) request('organiser_profile_id') === (string) $profile->id)>{{ $profile->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="status" class="ds-input xl:w-44">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="ds-button-secondary" type="submit"><x-ui.icon name="filter" class="size-4" /> Apply</button>
        </form>
    </x-ui.card>

    <x-ui.card padding="p-0" class="mt-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                    <tr>
                        <th class="w-10 px-4 py-3 text-center">#</th>
                        <th class="px-4 py-3">Event Name</th>
                        <th class="whitespace-nowrap px-4 py-3">Event Date</th>
                        <th class="w-px whitespace-nowrap px-4 py-3">Status</th>
                        <th class="w-px px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $event)
                        @php
                            $eventStatus = $event->status_key instanceof \BackedEnum ? $event->status_key->value : $event->status_key;
                            $statusBadge = match($eventStatus) {
                                'published' => 'bg-emerald-50 text-emerald-700',
                                'submitted' => 'bg-blue-50 text-blue-700',
                                default     => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3 text-center text-xs font-medium text-slate-400">
                                {{ $events->firstItem() + $loop->index }}
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('core.events.show', $event) }}" class="font-bold text-slate-950 hover:text-blue-700">{{ $event->title }}</a>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $event->slug }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                @if($event->starts_at)
                                    <span class="font-medium">{{ $event->starts_at->format('d M Y') }}</span>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $event->starts_at->format('H:i') }}{{ $event->ends_at ? ' – '.$event->ends_at->format('H:i') : '' }}</p>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="w-px whitespace-nowrap px-4 py-3">
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusBadge }}">{{ ucfirst($eventStatus) }}</span>
                            </td>
                            <td class="w-px whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('core.events.show', $event) }}" class="ds-button-secondary">Open Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10">
                                <x-ui.empty-state icon="calendar" title="No events found" description="Create an event and assign it to an organiser profile before configuring settings and tickets." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($events->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $events->links() }}
            </div>
        @endif
    </x-ui.card>
</x-layouts.admin>
