<x-layouts.admin title="Edit User" heading="Edit User" subheading="{{ $user->email }}">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @method('PUT')
        @include('admin.users._form', ['button' => 'Update user'])
    </form>
</x-layouts.admin>
