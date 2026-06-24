<x-layouts.admin title="Edit Organiser Profile" heading="Edit Organiser Profile" eyebrow="Organiser Profile">
    <x-ui.page-header
        eyebrow="Edit Profile"
        title="{{ $profile->name }}"
        description="Update organiser sender identity and profile details."
    >
        <x-slot:actions>
            <a href="{{ route('core.organisers.show', $profile) }}" class="ds-button-secondary">View profile</a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('core.organisers.update', $profile) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.core.organisers._form', ['profile' => $profile])
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('core.organisers.show', $profile) }}" class="ds-button-secondary">Cancel</a>
            <button class="ds-button-primary" type="submit">Update Profile</button>
        </div>
    </form>
</x-layouts.admin>
