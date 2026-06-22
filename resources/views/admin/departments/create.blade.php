<x-layouts.admin title="Create Department" heading="Create Department" subheading="Add an organization unit">
    <form method="POST" action="{{ route('admin.departments.store') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @include('admin.departments._form', ['button' => 'Create department'])
    </form>
</x-layouts.admin>
