<x-layouts.admin title="Edit Role" heading="Edit Role" subheading="{{ $role->name }}">
    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @method('PUT')
        @include('admin.roles._form', ['button' => 'Update role'])
    </form>
</x-layouts.admin>
