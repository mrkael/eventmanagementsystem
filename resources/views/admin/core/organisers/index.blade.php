<x-layouts.admin title="Organiser Profile" heading="Organiser Profile" subheading="Sender identity and event ownership">
    <form method="POST" action="{{ route('core.organisers.store') }}" enctype="multipart/form-data" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        <div class="grid gap-4 lg:grid-cols-3">
            <label class="block"><span class="text-sm font-medium">Organiser name</span><input name="name" required class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Organiser email</span><input type="email" name="email" required class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Phone</span><input name="phone" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Website</span><input type="url" name="website" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Logo</span><input type="file" name="logo" accept="image/*" class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2"></label>
            <label class="block"><span class="text-sm font-medium">User view</span><select name="user_ids[]" multiple class="mt-1 min-h-24 w-full rounded-lg border-slate-300">@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }} · {{ $user->email }}</option>@endforeach</select></label>
            <label class="block lg:col-span-3"><span class="text-sm font-medium">Address</span><textarea name="address" rows="2" class="mt-1 w-full rounded-lg border-slate-300"></textarea></label>
        </div>
        <div class="mt-5 flex justify-end"><button class="min-h-11 rounded-lg bg-emerald-700 px-5 text-sm font-semibold text-white">Save profile</button></div>
    </form>
    <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b px-5 py-4"><h2 class="font-semibold">Profiles</h2></div>
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500"><tr><th class="px-5 py-3">Name</th><th class="px-5 py-3">Email</th><th class="px-5 py-3">Events</th><th class="px-5 py-3">Assigned users</th><th class="px-5 py-3">Status</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($profiles as $profile)<tr><td class="px-5 py-4 font-medium">{{ $profile->name }}</td><td class="px-5 py-4">{{ $profile->email }}</td><td class="px-5 py-4">{{ $profile->events_count }}</td><td class="px-5 py-4">{{ $profile->users_count }}</td><td class="px-5 py-4">{{ $profile->is_active ? 'Active' : 'Inactive' }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No organiser profiles yet.</td></tr>@endforelse</tbody></table></div>
    </section>
    <div class="mt-5">{{ $profiles->links() }}</div>
</x-layouts.admin>
