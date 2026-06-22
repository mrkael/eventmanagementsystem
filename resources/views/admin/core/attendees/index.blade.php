<x-layouts.admin title="Attendees" heading="Attendees" subheading="Search, filter, export, resend, and cancel registrations">
    <form class="mb-5 grid gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-5">
        <input name="search" value="{{ request('search') }}" placeholder="Name, email, reference" class="rounded-lg border-slate-300">
        <select name="event_id" class="rounded-lg border-slate-300"><option value="">All events</option>@foreach($events as $event)<option value="{{ $event->id }}" @selected(request('event_id') == $event->id)>{{ $event->title }}</option>@endforeach</select>
        <select name="ticket_id" class="rounded-lg border-slate-300"><option value="">All tickets</option>@foreach($tickets as $ticket)<option value="{{ $ticket->id }}" @selected(request('ticket_id') == $ticket->id)>{{ $ticket->name }}</option>@endforeach</select>
        <select name="status" class="rounded-lg border-slate-300"><option value="">All statuses</option>@foreach(['pending','confirmed','waitlisted','cancelled','attended','no_show'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select>
        <button class="rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white">Filter</button>
    </form>
    <div class="mb-4"><a href="{{ route('core.attendees.export', request()->query()) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">Export CSV</a></div>
    <div class="mb-6 grid gap-4 lg:grid-cols-2">
        <form method="POST" action="{{ route('core.attendees.store') }}" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            @csrf
            <h2 class="mb-3 font-semibold">Add participant manually</h2>
            <div class="grid gap-3 md:grid-cols-2">
                <select name="event_id" required class="rounded-lg border-slate-300"><option value="">Event</option>@foreach($events as $event)<option value="{{ $event->id }}">{{ $event->title }}</option>@endforeach</select>
                <input name="ticket_id" required placeholder="Ticket ID" class="rounded-lg border-slate-300">
                <input name="full_name" required placeholder="Full name" class="rounded-lg border-slate-300">
                <input type="email" name="email" required placeholder="Email" class="rounded-lg border-slate-300">
                <input name="phone" placeholder="Phone" class="rounded-lg border-slate-300">
                <input name="organization" placeholder="Organization" class="rounded-lg border-slate-300">
            </div>
            <button class="mt-4 min-h-11 rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white">Register participant</button>
        </form>
        <form method="POST" action="{{ route('core.attendees.import') }}" enctype="multipart/form-data" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            @csrf
            <h2 class="mb-3 font-semibold">Import CSV</h2>
            <div class="grid gap-3 md:grid-cols-2">
                <select name="event_id" required class="rounded-lg border-slate-300"><option value="">Event</option>@foreach($events as $event)<option value="{{ $event->id }}">{{ $event->title }}</option>@endforeach</select>
                <input name="ticket_id" required placeholder="Ticket ID" class="rounded-lg border-slate-300">
                <input type="file" name="file" accept=".csv,text/csv" required class="rounded-lg border border-slate-300 bg-white p-2 md:col-span-2">
            </div>
            <p class="mt-3 text-xs text-slate-500">CSV headers: full_name,email,phone,organization,designation</p>
            <button class="mt-4 min-h-11 rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white">Import participants</button>
        </form>
    </div>
    <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500"><tr><th class="px-5 py-3">Reference</th><th class="px-5 py-3">Participant</th><th class="px-5 py-3">Event</th><th class="px-5 py-3">Ticket</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Actions</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($registrations as $registration)<tr><td class="px-5 py-4 font-medium">{{ $registration->reference_number }}</td><td class="px-5 py-4"><p>{{ $registration->full_name }}</p><p class="text-xs text-slate-500">{{ $registration->email }}</p></td><td class="px-5 py-4">{{ $registration->event?->title }}</td><td class="px-5 py-4">{{ $registration->ticket?->name }}</td><td class="px-5 py-4">{{ ucfirst($registration->status) }}</td><td class="px-5 py-4"><div class="flex gap-2"><form method="POST" action="{{ route('core.attendees.resend', $registration) }}">@csrf<button class="rounded border px-2 py-1 text-xs font-semibold">Resend</button></form><form method="POST" action="{{ route('core.attendees.cancel', $registration) }}">@csrf @method('PATCH')<button class="rounded border px-2 py-1 text-xs font-semibold text-red-700">Cancel</button></form></div></td></tr>@empty<tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">No attendees found.</td></tr>@endforelse</tbody></table></div>
    </section>
    <div class="mt-5">{{ $registrations->links() }}</div>
</x-layouts.admin>
