@php($event = $event ?? null)
@php($eventStatus = $event?->status_key instanceof \BackedEnum ? $event->status_key->value : ($event?->status_key ?? 'draft'))

<div class="grid gap-6 xl:grid-cols-[1fr_20rem]">
    <div class="space-y-6">
        <x-ui.card>
            <h2 class="text-xl font-semibold text-slate-950">Event settings</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <label>
                    <span class="ds-label">Event Name</span>
                    <input name="title" value="{{ old('title', $event?->title) }}" required class="ds-input mt-2" autocomplete="off">
                    @error('title')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Organiser Profile</span>
                    @if($isPlatformAdmin ?? false)
                        <select name="organiser_profile_id" required class="ds-input mt-2">
                            <option value="">Select organiser</option>
                            @foreach(($organiserProfiles ?? collect()) as $profile)
                                <option value="{{ $profile->id }}" @selected(old('organiser_profile_id', $event?->organiser_profile_id) == $profile->id)>{{ $profile->name }} - {{ $profile->email }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="hidden" name="organiser_profile_id" value="{{ $ownOrganiserProfile?->id }}">
                        <div class="mt-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                            {{ $ownOrganiserProfile?->name ?? 'No organiser profile linked' }}
                            @if($ownOrganiserProfile?->email)
                                <span class="block pt-1 text-xs font-semibold text-slate-500">{{ $ownOrganiserProfile->email }}</span>
                            @endif
                        </div>
                    @endif
                    @error('organiser_profile_id')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Slug</span>
                    <input name="slug" value="{{ old('slug', $event?->slug) }}" required pattern="[a-z0-9-]+" class="ds-input mt-2" placeholder="annual-leadership-forum">
                    @error('slug')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Event Site URL</span>
                    <input name="custom_url" value="{{ old('custom_url', $event?->custom_url) }}" pattern="[a-z0-9-]+" class="ds-input mt-2" placeholder="annual-leadership-forum-2027">
                    @error('custom_url')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                    <span class="mt-2 block text-xs font-medium text-slate-500">{{ url('/e/'.(old('custom_url', $event?->custom_url) ?: 'your-event-url')) }}</span>
                </label>
                <label>
                    <span class="ds-label">Event Start Date</span>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($event?->starts_at)->format('Y-m-d\TH:i')) }}" required class="ds-input mt-2">
                    @error('starts_at')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Event End Date</span>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($event?->ends_at)->format('Y-m-d\TH:i')) }}" required class="ds-input mt-2">
                    @error('ends_at')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Registration Open Date</span>
                    <input type="datetime-local" name="registration_opens_at" value="{{ old('registration_opens_at', optional($event?->registration_opens_at)->format('Y-m-d\TH:i')) }}" class="ds-input mt-2">
                    @error('registration_opens_at')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Registration Close Date</span>
                    <input type="datetime-local" name="registration_closes_at" value="{{ old('registration_closes_at', optional($event?->registration_closes_at)->format('Y-m-d\TH:i')) }}" class="ds-input mt-2">
                    @error('registration_closes_at')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Event Location</span>
                    <input name="location" value="{{ old('location', $event?->location) }}" class="ds-input mt-2" autocomplete="off">
                    @error('location')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="ds-label">Event Status</span>
                    <select name="status_key" class="ds-input mt-2">
                        <option value="draft" @selected(old('status_key', $eventStatus) === 'draft')>Draft</option>
                        <option value="submitted" @selected(old('status_key', $eventStatus) === 'submitted')>Submitted</option>
                        <option value="published" @selected(old('status_key', $eventStatus) === 'published')>Published</option>
                    </select>
                    @error('status_key')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
                <label class="md:col-span-2">
                    <span class="ds-label">Event Description</span>
                    <textarea name="description" rows="6" class="ds-input mt-2 min-h-40 py-3">{{ old('description', $event?->description) }}</textarea>
                    @error('description')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>
        </x-ui.card>
    </div>

    <div class="space-y-6">
        <x-ui.card>
            <h2 class="text-xl font-semibold text-slate-950">Registration option</h2>
            <input type="hidden" name="allow_duplicate_email_registration" value="0">
            <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-[22px] border border-slate-200 bg-slate-50 p-4">
                <input type="checkbox" name="allow_duplicate_email_registration" value="1" @checked(old('allow_duplicate_email_registration', $event?->allow_duplicate_email ?? false)) class="mt-1 rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                <span>
                    <span class="block text-sm font-bold text-slate-950">Allow Multiple Email Registration</span>
                    <span class="mt-1 block text-sm leading-6 text-slate-500">When enabled, the same email can register more than once for this event in a future registration flow.</span>
                </span>
            </label>
            @error('allow_duplicate_email_registration')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
        </x-ui.card>
    </div>
</div>
