<x-layouts.admin title="Create Permission" heading="Create Permission" subheading="Add a capability key">
    <form method="POST" action="{{ route('admin.permissions.store') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @include('admin.permissions._form', ['button' => 'Create permission'])
    </form>
</x-layouts.admin>
