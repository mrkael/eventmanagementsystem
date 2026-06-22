<x-layouts.admin title="Create User" heading="Create User" subheading="Provision a back-office account">
    <form method="POST" action="{{ route('admin.users.store') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @include('admin.users._form', ['button' => 'Create user'])
    </form>
</x-layouts.admin>
