<x-layouts.admin title="Edit Permission" heading="Edit Permission" subheading="{{ $permission->key }}">
    <form method="POST" action="{{ route('admin.permissions.update', $permission) }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @method('PUT')
        @include('admin.permissions._form', ['button' => 'Update permission'])
    </form>
</x-layouts.admin>
