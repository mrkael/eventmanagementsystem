<x-layouts.admin title="Create Event Category" heading="Create Event Category" subheading="Add a classification option">
    <form method="POST" action="{{ route('admin.event-categories.store') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @include('admin.event-setup.categories._form', ['button' => 'Create category'])
    </form>
</x-layouts.admin>
