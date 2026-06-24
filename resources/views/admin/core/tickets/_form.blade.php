@php($ticket = $ticket ?? null)

<div class="grid gap-6 xl:grid-cols-[1fr_20rem]">
    <div class="space-y-6">
        <x-ui.card>
            <h2 class="text-xl font-semibold text-slate-950">Ticket information</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <label>
                    <span class="ds-label">Ticket Name</span>
                    <input name="name" value="{{ old('name', $ticket?->name) }}" required class="ds-input mt-2">
                    @error('name')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Quantity</span>
                    <input type="number" min="1" name="quantity" value="{{ old('quantity', $ticket?->quantity) }}" required class="ds-input mt-2">
                    @error('quantity')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Min Quantity</span>
                    <input type="number" min="1" name="min_quantity" value="{{ old('min_quantity', $ticket?->min_quantity ?? 1) }}" required class="ds-input mt-2">
                    @error('min_quantity')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Max Quantity</span>
                    <input type="number" min="1" name="max_quantity" value="{{ old('max_quantity', $ticket?->max_quantity ?? 1) }}" required class="ds-input mt-2">
                    @error('max_quantity')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Status</span>
                    <select name="status" class="ds-input mt-2">
                        <option value="active" @selected(old('status', $ticket?->status ?? 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $ticket?->status) === 'inactive')>Inactive</option>
                    </select>
                    @error('status')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Ticket Active Date From</span>
                    <input type="datetime-local" name="sales_start_at" value="{{ old('sales_start_at', optional($ticket?->sales_start_at)->format('Y-m-d\TH:i')) }}" required class="ds-input mt-2">
                    @error('sales_start_at')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Ticket Active Date Until</span>
                    <input type="datetime-local" name="sales_end_at" value="{{ old('sales_end_at', optional($ticket?->sales_end_at)->format('Y-m-d\TH:i')) }}" required class="ds-input mt-2">
                    @error('sales_end_at')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label class="md:col-span-2">
                    <span class="ds-label">Description</span>
                    <textarea name="description" rows="5" class="ds-input mt-2 min-h-36 py-3">{{ old('description', $ticket?->description) }}</textarea>
                    @error('description')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>
        </x-ui.card>
    </div>

    <div class="space-y-6">
        <x-ui.card>
            <h2 class="text-xl font-semibold text-slate-950">Visibility</h2>
            <input type="hidden" name="is_hidden" value="0">
            <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-[22px] border border-slate-200 bg-slate-50 p-4">
                <input type="checkbox" name="is_hidden" value="1" @checked(old('is_hidden', $ticket?->is_hidden ?? false)) class="mt-1 rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                <span>
                    <span class="block text-sm font-bold text-slate-950">Hidden Ticket</span>
                    <span class="mt-1 block text-sm leading-6 text-slate-500">Hidden tickets will be excluded from the future public registration page.</span>
                </span>
            </label>
            @error('is_hidden')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
        </x-ui.card>
    </div>
</div>
