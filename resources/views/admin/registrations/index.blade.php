<x-layouts.admin title="Participants" heading="Participants" subheading="{{ $event->title }}">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.events.show', $event) }}" class="min-h-11 rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold hover:bg-slate-50">Event detail</a>
            <a href="{{ route('admin.events.registrations.builder.edit', $event) }}" class="min-h-11 rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold hover:bg-slate-50">Form builder</a>
            <a href="{{ route('admin.events.registrations.create', $event) }}" class="min-h-11 rounded-lg bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-800">Admin registration</a>
        </div>
    </div>

    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" class="grid gap-3 md:grid-cols-[1fr_180px_180px_auto]">
            <input name="search" value="{{ request('search') }}" placeholder="Search name or email" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm">
            <select name="status" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm"><option value="">All statuses</option>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(request('status')===$status->value)>{{ $status->label() }}</option>@endforeach</select>
            <select name="source" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm"><option value="">All sources</option>@foreach(['public','private','invite','admin','bulk'] as $source)<option value="{{ $source }}" @selected(request('source')===$source)>{{ ucfirst($source) }}</option>@endforeach</select>
            <button class="min-h-11 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white">Filter</button>
        </form>
    </section>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1fr_340px]">
        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Participant</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Source</th><th class="px-4 py-3">Registered</th><th class="px-4 py-3"></th></tr></thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($registrations as $registration)
                            <tr>
                                <td class="px-4 py-3"><p class="font-semibold">{{ $registration->name }}</p><p class="text-slate-500">{{ $registration->email }}</p></td>
                                <td class="px-4 py-3">{{ $registration->status->label() }}</td>
                                <td class="px-4 py-3">{{ ucfirst($registration->source) }}</td>
                                <td class="px-4 py-3">{{ $registration->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('admin.events.registrations.show', [$event, $registration]) }}" class="font-semibold text-emerald-700">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No participants found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 p-4">{{ $registrations->links() }}</div>
        </section>

        <aside class="space-y-5">
            <form method="POST" action="{{ route('admin.events.registrations.bulk', $event) }}" enctype="multipart/form-data" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                @csrf
                <h2 class="font-semibold">Bulk upload</h2>
                <p class="mt-1 text-sm text-slate-500">CSV headers: name, email, phone, organization.</p>
                <input type="file" name="file" required class="mt-4 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button class="mt-3 min-h-11 w-full rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white">Import CSV</button>
            </form>
            <form method="POST" action="{{ route('admin.events.registrations.invite', $event) }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                @csrf
                <h2 class="font-semibold">Invite registration</h2>
                <div class="mt-4 space-y-3">
                    <input name="name" placeholder="Name" class="min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm">
                    <input type="email" name="email" placeholder="Email" required class="min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm">
                    <input type="date" name="expires_at" class="min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm">
                    <button class="min-h-11 w-full rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white">Create invite</button>
                </div>
            </form>
        </aside>
    </div>
</x-layouts.admin>
