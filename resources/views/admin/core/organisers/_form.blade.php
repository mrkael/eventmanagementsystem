@php($profile = $profile ?? null)

<div class="grid gap-6 xl:grid-cols-[1fr_20rem]">
    <div class="space-y-6">
        <x-ui.card>
            <h2 class="text-xl font-semibold text-slate-950">Organiser details</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <label>
                    <span class="ds-label">Organiser Name</span>
                    <input name="name" value="{{ old('name', $profile?->name) }}" required class="ds-input mt-2" autocomplete="organization">
                    @error('name')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Organiser Email</span>
                    <input type="email" name="email" value="{{ old('email', $profile?->email) }}" required class="ds-input mt-2" autocomplete="email">
                    @error('email')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Phone Number</span>
                    <input name="phone" value="{{ old('phone', $profile?->phone) }}" class="ds-input mt-2" autocomplete="tel">
                    @error('phone')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Website</span>
                    <input type="url" name="website" value="{{ old('website', $profile?->website) }}" class="ds-input mt-2" placeholder="https://example.com" autocomplete="url">
                    @error('website')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label class="md:col-span-2">
                    <span class="ds-label">Address</span>
                    <textarea name="address" rows="3" class="ds-input mt-2 min-h-28 py-3">{{ old('address', $profile?->address) }}</textarea>
                    @error('address')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label class="md:col-span-2">
                    <span class="ds-label">Description</span>
                    <textarea name="description" rows="5" class="ds-input mt-2 min-h-36 py-3">{{ old('description', $profile?->description) }}</textarea>
                    @error('description')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-xl font-semibold text-slate-950">Sender email usage</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">The organiser email is reserved as the sender email for future confirmation emails, email invitations, and registration form emails.</p>
        </x-ui.card>
    </div>

    <div class="space-y-6">
        <x-ui.card>
            <h2 class="text-xl font-semibold text-slate-950">Logo</h2>
            <div class="mt-5 rounded-[22px] border border-dashed border-slate-300 bg-slate-50/80 p-5 text-center">
                @if($profile?->logo_path)
                    <img src="{{ asset('storage/'.$profile->logo_path) }}" alt="{{ $profile->name }} logo" class="mx-auto size-24 rounded-2xl object-cover">
                @else
                    <div class="mx-auto grid size-24 place-items-center rounded-2xl bg-white text-slate-400">
                        <x-ui.icon name="upload" class="size-7" />
                    </div>
                @endif
                <input type="file" name="logo" accept="image/*" class="mt-5 w-full rounded-2xl border border-slate-200 bg-white p-3 text-sm">
                @error('logo')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
            </div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-xl font-semibold text-slate-950">Status</h2>
            <label class="mt-5 block">
                <span class="ds-label">Profile Status</span>
                <select name="status" class="ds-input mt-2">
                    <option value="active" @selected(old('status', $profile?->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $profile?->status) === 'inactive')>Inactive</option>
                </select>
                @error('status')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
            </label>
        </x-ui.card>
    </div>
</div>
