<x-layouts.admin title="Events" heading="Events" subheading="Manage concurrent events and microsites">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form class="flex gap-2"><input name="search" value="{{ request('search') }}" placeholder="Search events" class="min-h-11 rounded-lg border-slate-300"><button class="rounded-lg border border-slate-300 px-4 text-sm font-semibold">Search</button></form>
        <a href="{{ route('core.events.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white">Create event</a>
    </div>
    <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
        @forelse($events as $event)
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                @php($status = $event->status_key instanceof \BackedEnum ? $event->status_key->value : $event->status_key)
                <div class="flex items-start justify-between gap-3"><h2 class="font-semibold">{{ $event->title }}</h2><span class="rounded-full bg-slate-100 px-2 py-1 text-xs">{{ $status }}</span></div>
                <p class="mt-2 text-sm text-slate-600">{{ $event->starts_at->format('d M Y, H:i') }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $event->location }}</p>
                <div class="mt-4 flex flex-wrap gap-2 text-sm">
                    <a class="rounded-lg border border-slate-300 px-3 py-2 font-semibold" href="{{ route('core.events.show', $event) }}">Open</a>
                    <a class="rounded-lg border border-slate-300 px-3 py-2 font-semibold" href="{{ route('core.events.microsite.edit', $event) }}">Microsite</a>
                    @if($event->custom_url)
                        <a class="rounded-lg border border-slate-300 px-3 py-2 font-semibold" href="{{ route('core.public.events.show', $event) }}" target="_blank">Public page</a>
                    @else
                        <span class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-400">Set URL first</span>
                    @endif
                </div>
            </article>
        @empty
            <p class="rounded-lg border border-slate-200 bg-white p-6 text-slate-500">No events yet.</p>
        @endforelse
    </div>
    <div class="mt-5">{{ $events->links() }}</div>
</x-layouts.admin>
