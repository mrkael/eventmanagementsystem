<x-layouts.admin title="Edit Department" heading="Edit Department" subheading="{{ $department->name }}">
    <form method="POST" action="{{ route('admin.departments.update', $department) }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @method('PUT')
        @include('admin.departments._form', ['button' => 'Update department'])
    </form>
</x-layouts.admin>
