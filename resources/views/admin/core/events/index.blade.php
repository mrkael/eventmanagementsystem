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
            <select name="organiser_profile_id" class="ds-input xl:w-64">
                <option value="">All organisers</option>
                @foreach($organiserProfiles as $profile)
                    <option value="{{ $profile->id }}" @selected((string) request('organiser_profile_id') === (string) $profile->id)>{{ $profile->name }}</option>
                @endforeach
            </select>
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
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Event Name</th>
                        <th class="px-5 py-4">Organiser Profile</th>
                        <th class="px-5 py-4">Event URL</th>
                        <th class="px-5 py-4">Event Date</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Total Tickets</th>
                        <th class="px-5 py-4">Created Date</th>
                        <th class="px-5 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $event)
                        @php($eventStatus = $event->status_key instanceof \BackedEnum ? $event->status_key->value : $event->status_key)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <a href="{{ route('core.events.show', $event) }}" class="font-bold text-slate-950 hover:text-blue-700">{{ $event->title }}</a>
                                <p class="mt-1 text-xs text-slate-500">{{ $event->slug }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $event->organiserProfile?->name ?? 'Not assigned' }}</td>
                            <td class="px-5 py-4">
                                @if($event->custom_url)
                                    <span class="font-mono text-xs text-slate-700">{{ url('/e/'.$event->custom_url) }}</span>
                                @else
                                    <span class="font-mono text-xs text-slate-400">{{ url('/e/your-event-url') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $event->starts_at?->format('d M Y, H:i') }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">{{ ucfirst($eventStatus) }}</span></td>
                            <td class="px-5 py-4 font-semibold text-slate-700">{{ number_format($event->tickets_count) }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $event->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('core.events.show', $event) }}" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-white">Open Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10">
                                <x-ui.empty-state icon="calendar" title="No events found" description="Create an event and assign it to an organiser profile before configuring settings and tickets." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-6">{{ $events->links() }}</div>
</x-layouts.admin>
