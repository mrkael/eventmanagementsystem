<x-layouts.admin title="{{ $profile->name }}" heading="Organiser Profile Detail" eyebrow="Organiser Profile">
    <x-ui.page-header
        eyebrow="Profile detail"
        title="{{ $profile->name }}"
        description="This organiser email is reserved as the sender email for future confirmation emails, email invitations, and registration form emails."
    >
        <x-slot:actions>
            <a href="{{ route('core.organisers.index') }}" class="ds-button-secondary">Back to listing</a>
            <a href="{{ route('core.organisers.edit', $profile) }}" class="ds-button-primary">Edit Profile</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 xl:grid-cols-[20rem_1fr]">
        <x-ui.card class="h-max text-center">
            @if($profile->logo_path)
                <img src="{{ asset('storage/'.$profile->logo_path) }}" alt="{{ $profile->name }} logo" class="mx-auto size-28 rounded-[28px] object-cover">
            @else
                <div class="mx-auto grid size-28 place-items-center rounded-[28px] bg-slate-100 text-4xl font-bold text-slate-500">{{ \Illuminate\Support\Str::of($profile->name)->substr(0, 1)->upper() }}</div>
            @endif
            <h2 class="mt-5 text-xl font-semibold text-slate-950">{{ $profile->name }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $profile->email }}</p>
            <span class="mt-5 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $profile->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($profile->status) }}</span>
        </x-ui.card>

        <div class="space-y-6">
            <x-ui.card>
                <h2 class="text-xl font-semibold text-slate-950">Profile information</h2>
                <dl class="mt-5 grid gap-5 md:grid-cols-2">
                    <div><dt class="ds-label">Organiser Name</dt><dd class="mt-2 font-semibold text-slate-950">{{ $profile->name }}</dd></div>
                    <div><dt class="ds-label">Organiser Email</dt><dd class="mt-2 font-semibold text-slate-950">{{ $profile->email }}</dd></div>
                    <div><dt class="ds-label">Phone Number</dt><dd class="mt-2 text-slate-700">{{ $profile->phone ?: '-' }}</dd></div>
                    <div><dt class="ds-label">Website</dt><dd class="mt-2">@if($profile->website)<a href="{{ $profile->website }}" target="_blank" class="font-semibold text-blue-700">{{ $profile->website }}</a>@else<span class="text-slate-500">-</span>@endif</dd></div>
                    <div><dt class="ds-label">Assigned Events Count</dt><dd class="mt-2 font-semibold text-slate-950">{{ number_format($profile->events_count) }}</dd></div>
                    <div><dt class="ds-label">Status</dt><dd class="mt-2 text-slate-700">{{ ucfirst($profile->status) }}</dd></div>
                </dl>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-xl font-semibold text-slate-950">Address and description</h2>
                <div class="mt-5 space-y-5">
                    <div><p class="ds-label">Address</p><p class="mt-2 whitespace-pre-line text-slate-700">{{ $profile->address ?: '-' }}</p></div>
                    <div><p class="ds-label">Description</p><p class="mt-2 whitespace-pre-line text-slate-700">{{ $profile->description ?: '-' }}</p></div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-xl font-semibold text-slate-950">Audit information</h2>
                <dl class="mt-5 grid gap-5 md:grid-cols-2">
                    <div><dt class="ds-label">Created By</dt><dd class="mt-2 text-slate-700">{{ $profile->creator?->name ?? 'System' }}</dd></div>
                    <div><dt class="ds-label">Updated By</dt><dd class="mt-2 text-slate-700">{{ $profile->updater?->name ?? 'System' }}</dd></div>
                    <div><dt class="ds-label">Created Date</dt><dd class="mt-2 text-slate-700">{{ $profile->created_at->format('d M Y, H:i') }}</dd></div>
                    <div><dt class="ds-label">Updated Date</dt><dd class="mt-2 text-slate-700">{{ $profile->updated_at->format('d M Y, H:i') }}</dd></div>
                </dl>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
