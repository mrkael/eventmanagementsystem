<x-layouts.admin title="Create Role" heading="Create Role" subheading="Create a reusable access profile">
    <form method="POST" action="{{ route('admin.roles.store') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @include('admin.roles._form', ['button' => 'Create role'])
    </form>
</x-layouts.admin>
