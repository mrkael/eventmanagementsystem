<x-layouts.admin title="Sessions" heading="Sessions & Check-In" subheading="{{ $event->title }}">
    <form method="POST" action="{{ route('core.events.sessions.store', $event) }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        <div class="grid gap-4 lg:grid-cols-3">
            <label class="block"><span class="text-sm font-medium">Session name</span><input name="title" required class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Start</span><input type="datetime-local" name="starts_at" required class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">End</span><input type="datetime-local" name="ends_at" required class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Location</span><input name="location" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Status</span><select name="status" class="mt-1 w-full rounded-lg border-slate-300"><option value="active">Active</option><option value="inactive">Inactive</option></select></label>
            <div class="flex items-end gap-4 text-sm"><input type="hidden" name="one_time_check_in" value="0"><label class="flex gap-2"><input type="checkbox" name="one_time_check_in" value="1" checked> One-time check-in</label><input type="hidden" name="checkout_enabled" value="0"><label class="flex gap-2"><input type="checkbox" name="checkout_enabled" value="1"> Check-out enabled</label></div>
            <label class="block lg:col-span-3"><span class="text-sm font-medium">Allowed tickets</span><select name="ticket_ids[]" multiple required class="mt-1 min-h-32 w-full rounded-lg border-slate-300">@foreach($tickets as $ticket)<option value="{{ $ticket->id }}">{{ $ticket->name }}</option>@endforeach</select></label>
            <label class="block lg:col-span-3"><span class="text-sm font-medium">Description</span><textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-300"></textarea></label>
        </div>
        <div class="mt-5 flex justify-end"><button class="min-h-11 rounded-lg bg-emerald-700 px-5 text-sm font-semibold text-white">Save session</button></div>
    </form>
    <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b px-5 py-4"><h2 class="font-semibold">Sessions</h2></div>
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500"><tr><th class="px-5 py-3">Session</th><th class="px-5 py-3">Time</th><th class="px-5 py-3">Tickets</th><th class="px-5 py-3">Rules</th><th class="px-5 py-3">Action</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($sessions as $session)<tr><td class="px-5 py-4 font-medium">{{ $session->title }}</td><td class="px-5 py-4">{{ $session->starts_at->format('d M Y H:i') }}</td><td class="px-5 py-4">{{ $session->tickets->pluck('name')->join(', ') }}</td><td class="px-5 py-4">{{ $session->one_time_check_in ? 'One-time' : 'Multiple' }}{{ $session->checkout_enabled ? ', check-out' : '' }}</td><td class="px-5 py-4"><a class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white" href="{{ route('core.events.sessions.scanner', [$event, $session]) }}">Open scanner</a></td></tr>@empty<tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No sessions yet.</td></tr>@endforelse</tbody></table></div>
    </section>
</x-layouts.admin>
