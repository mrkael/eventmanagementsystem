<x-layouts.admin title="Tickets" heading="Tickets & Promo Codes" subheading="{{ $event->title }}">
    <form method="POST" action="{{ route('core.events.tickets.store', $event) }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        <div class="grid gap-4 lg:grid-cols-3">
            <label class="block"><span class="text-sm font-medium">Ticket name</span><input name="name" required class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Registration form</span><select name="registration_form_id" required class="mt-1 w-full rounded-lg border-slate-300">@foreach($forms as $form)<option value="{{ $form->id }}">{{ $form->title }}</option>@endforeach</select></label>
            <label class="block"><span class="text-sm font-medium">Status</span><select name="status" class="mt-1 w-full rounded-lg border-slate-300"><option value="active">Active</option><option value="inactive">Inactive</option></select></label>
            <label class="block"><span class="text-sm font-medium">Currency</span><select name="currency" class="mt-1 w-full rounded-lg border-slate-300"><option>MYR</option><option>IDR</option><option>SGD</option><option>USD</option></select></label>
            <label class="block"><span class="text-sm font-medium">Price</span><input type="number" step="0.01" min="0" name="price" value="0" required class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Early-bird price</span><input type="number" step="0.01" min="0" name="early_bird_price" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Quantity</span><input type="number" min="1" name="quantity" required class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Min quantity</span><input type="number" min="1" name="min_quantity" value="1" required class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Max quantity</span><input type="number" min="1" name="max_quantity" value="1" required class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Sales start</span><input type="datetime-local" name="sales_start_at" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Sales end</span><input type="datetime-local" name="sales_end_at" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="flex items-end gap-2 text-sm"><input type="checkbox" name="is_hidden" value="1"> Hidden ticket</label>
            <label class="block lg:col-span-3"><span class="text-sm font-medium">Description</span><textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-300"></textarea></label>
        </div>
        <div class="mt-5 flex justify-end"><button class="min-h-11 rounded-lg bg-emerald-700 px-5 text-sm font-semibold text-white">Save ticket</button></div>
    </form>
    <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b px-5 py-4"><h2 class="font-semibold">Tickets</h2></div>
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500"><tr><th class="px-5 py-3">Name</th><th class="px-5 py-3">Form</th><th class="px-5 py-3">Price</th><th class="px-5 py-3">Available</th><th class="px-5 py-3">Visibility</th><th class="px-5 py-3">Status</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($tickets as $ticket)<tr><td class="px-5 py-4 font-medium">{{ $ticket->name }}</td><td class="px-5 py-4">{{ $ticket->form?->title }}</td><td class="px-5 py-4">{{ $ticket->currency }} {{ number_format($ticket->price, 2) }}</td><td class="px-5 py-4">{{ $ticket->available_quantity }} / {{ $ticket->quantity }}</td><td class="px-5 py-4">{{ $ticket->is_hidden ? 'Hidden' : 'Public' }}</td><td class="px-5 py-4">{{ ucfirst($ticket->status) }}</td></tr>@empty<tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">No tickets yet.</td></tr>@endforelse</tbody></table></div>
    </section>
    <form method="POST" action="{{ route('core.events.promos.store', $event) }}" class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        <h2 class="mb-4 font-semibold">Create promo code</h2>
        <div class="grid gap-4 lg:grid-cols-4">
            <label class="block"><span class="text-sm font-medium">Code</span><input name="code" required class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Ticket</span><select name="ticket_id" class="mt-1 w-full rounded-lg border-slate-300"><option value="">Any ticket</option>@foreach($event->tickets as $ticket)<option value="{{ $ticket->id }}">{{ $ticket->name }}</option>@endforeach</select></label>
            <label class="block"><span class="text-sm font-medium">Discount type</span><select name="discount_type" class="mt-1 w-full rounded-lg border-slate-300"><option value="fixed">Fixed</option><option value="percentage">Percentage</option></select></label>
            <label class="block"><span class="text-sm font-medium">Discount value</span><input type="number" step="0.01" min="0" name="discount_value" required class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Usage limit</span><input type="number" min="1" name="usage_limit" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Valid from</span><input type="datetime-local" name="valid_from" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Valid until</span><input type="datetime-local" name="valid_until" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="flex items-end gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label>
        </div>
        <div class="mt-5 flex justify-end"><button class="min-h-11 rounded-lg bg-emerald-700 px-5 text-sm font-semibold text-white">Save promo</button></div>
    </form>
</x-layouts.admin>
