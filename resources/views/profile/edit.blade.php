<x-layouts.admin title="Profile" heading="User Profile" subheading="Manage your account details">
    <form method="POST" action="{{ route('profile.update') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Full name</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-slate-700">Phone</label>
                <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
            </div>
            <div>
                <label for="position" class="block text-sm font-medium text-slate-700">Position</label>
                <input id="position" name="position" value="{{ old('position', $user->position) }}" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Department</label>
                <p class="mt-2 min-h-11 rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-600">{{ $user->department?->name ?? 'Not assigned' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Roles</label>
                <p class="mt-2 min-h-11 rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-600">{{ $user->roles->pluck('name')->join(', ') ?: 'No roles assigned' }}</p>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">New password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm new password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
            </div>
        </div>
        <div class="mt-6">
            <button class="min-h-11 rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800">Update profile</button>
        </div>
    </form>
</x-layouts.admin>
