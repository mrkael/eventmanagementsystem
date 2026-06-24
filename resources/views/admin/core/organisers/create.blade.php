<x-layouts.admin title="Create Organiser Profile" heading="Create Organiser Profile" eyebrow="Organiser Profile">
    <x-ui.page-header
        eyebrow="Create New Profile"
        title="Create organiser profile"
        description="Only organiser name and organiser email are mandatory. The email will be used as the sender email for future event communications."
    >
        <x-slot:actions>
            <a href="{{ route('core.organisers.index') }}" class="ds-button-secondary">Back to listing</a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('core.organisers.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.core.organisers._form')
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('core.organisers.index') }}" class="ds-button-secondary">Cancel</a>
            <button class="ds-button-primary" type="submit">Save Profile</button>
        </div>
    </form>
</x-layouts.admin>
