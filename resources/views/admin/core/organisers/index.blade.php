<x-layouts.admin title="Organiser Profile" heading="Organiser Profile" eyebrow="Core module">
    <x-ui.page-header
        eyebrow="Sender identity"
        title="Organiser Profiles"
        description="Create and manage organiser identities. Each profile can be assigned to events later and its email will be used as the sender address for future event communications."
    >
        <x-slot:actions>
            <a href="{{ route('core.organisers.create') }}" class="ds-button-primary"><x-ui.icon name="plus" class="size-4" /> Create New Profile</a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card>
        <form method="GET" class="grid gap-3 lg:grid-cols-[1fr_auto_auto_auto]">
            <label class="relative">
                <x-ui.icon name="search" class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input name="search" value="{{ request('search') }}" class="ds-input pl-11" placeholder="Search organiser name or email">
            </label>
            <select name="status" class="ds-input lg:w-44">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            <select name="sort" class="ds-input lg:w-44">
                <option value="created_at" @selected($sort === 'created_at')>Sort by created date</option>
                <option value="name" @selected($sort === 'name')>Sort by name</option>
            </select>
            <button class="ds-button-secondary" type="submit">Apply</button>
            <input type="hidden" name="direction" value="{{ $direction }}">
        </form>
    </x-ui.card>

    <x-ui.card padding="p-0" class="mt-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4">
                            <a href="{{ route('core.organisers.index', array_merge(request()->except(['sort', 'direction', 'page']), ['sort' => 'name', 'direction' => $sort === 'name' && $direction === 'asc' ? 'desc' : 'asc'])) }}" class="inline-flex items-center gap-1">Organiser Name</a>
                        </th>
                        <th class="px-5 py-4">Organiser Email</th>
                        <th class="px-5 py-4">Phone Number</th>
                        <th class="px-5 py-4">Website</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Assigned Events</th>
                        <th class="px-5 py-4">
                            <a href="{{ route('core.organisers.index', array_merge(request()->except(['sort', 'direction', 'page']), ['sort' => 'created_at', 'direction' => $sort === 'created_at' && $direction === 'asc' ? 'desc' : 'asc'])) }}">Created Date</a>
                        </th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($profiles as $profile)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($profile->logo_path)
                                        <img src="{{ asset('storage/'.$profile->logo_path) }}" alt="{{ $profile->name }} logo" class="size-10 rounded-2xl object-cover">
                                    @else
                                        <div class="grid size-10 place-items-center rounded-2xl bg-slate-100 text-sm font-bold text-slate-500">{{ \Illuminate\Support\Str::of($profile->name)->substr(0, 1)->upper() }}</div>
                                    @endif
                                    <a href="{{ route('core.organisers.show', $profile) }}" class="font-bold text-slate-950 hover:text-blue-700">{{ $profile->name }}</a>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $profile->email }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $profile->phone ?: '-' }}</td>
                            <td class="px-5 py-4">@if($profile->website)<a href="{{ $profile->website }}" target="_blank" class="font-semibold text-blue-700">Open</a>@else<span class="text-slate-400">-</span>@endif</td>
                            <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $profile->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($profile->status) }}</span></td>
                            <td class="px-5 py-4 font-semibold text-slate-700">{{ number_format($profile->events_count) }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $profile->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('core.organisers.edit', $profile) }}" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-white">Edit</a>
                                    <form method="POST" action="{{ route('core.organisers.destroy', $profile) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-full border border-red-200 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10">
                                <x-ui.empty-state icon="building" title="No organiser profiles found" description="Create your first organiser profile to prepare sender identity for future event emails." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-6">{{ $profiles->links() }}</div>
</x-layouts.admin>
