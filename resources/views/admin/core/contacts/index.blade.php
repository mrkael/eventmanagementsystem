<x-layouts.admin title="Contacts" heading="Contacts" subheading="Groups and master list for invitations">
    <div class="grid gap-6 xl:grid-cols-[.8fr_1.2fr]">
        <div class="space-y-6">
            <form method="POST" action="{{ route('core.contacts.groups.store') }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                @csrf
                <h2 class="mb-4 font-semibold">Create group</h2>
                <label class="block"><span class="text-sm font-medium">Group name</span><input name="name" required class="mt-1 w-full rounded-lg border-slate-300"></label>
                <label class="mt-3 block"><span class="text-sm font-medium">Description</span><textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-300"></textarea></label>
                <button class="mt-4 min-h-11 rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white">Save group</button>
            </form>
            <form method="POST" action="{{ route('core.contacts.import') }}" enctype="multipart/form-data" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                @csrf
                <h2 class="mb-4 font-semibold">Import contacts</h2>
                <select name="group_id" class="w-full rounded-lg border-slate-300"><option value="">No group</option>@foreach($groups as $group)<option value="{{ $group->id }}">{{ $group->name }}</option>@endforeach</select>
                <input type="file" name="file" accept=".csv,text/csv" required class="mt-3 w-full rounded-lg border border-slate-300 bg-white p-2">
                <p class="mt-2 text-xs text-slate-500">CSV headers: first_name,last_name,email,mobile_number,organization,designation,department</p>
                <button class="mt-4 min-h-11 rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white">Upload CSV</button>
            </form>
        </div>
        <form method="POST" action="{{ route('core.contacts.store') }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            <h2 class="mb-4 font-semibold">Quick add contact</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <input name="first_name" required placeholder="First name" class="rounded-lg border-slate-300"><input name="last_name" placeholder="Last name" class="rounded-lg border-slate-300">
                <input type="email" name="email" placeholder="Email" class="rounded-lg border-slate-300"><input name="mobile_number" placeholder="Mobile number" class="rounded-lg border-slate-300">
                <input name="organization" placeholder="Organization" class="rounded-lg border-slate-300"><input name="designation" placeholder="Designation" class="rounded-lg border-slate-300">
                <input name="department" placeholder="Department" class="rounded-lg border-slate-300"><select name="group_ids[]" multiple class="min-h-24 rounded-lg border-slate-300">@foreach($groups as $group)<option value="{{ $group->id }}">{{ $group->name }}</option>@endforeach</select>
                <input name="secretary_name" placeholder="Secretary name" class="rounded-lg border-slate-300"><input type="email" name="secretary_email" placeholder="Secretary email" class="rounded-lg border-slate-300">
            </div>
            <button class="mt-4 min-h-11 rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white">Save contact</button>
        </form>
    </div>
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form class="flex gap-2"><input name="search" value="{{ request('search') }}" placeholder="Search master list" class="min-h-11 rounded-lg border-slate-300"><select name="group_id" class="rounded-lg border-slate-300"><option value="">All groups</option>@foreach($groups as $group)<option value="{{ $group->id }}" @selected(request('group_id') == $group->id)>{{ $group->name }} ({{ $group->contacts_count }})</option>@endforeach</select><button class="rounded-lg border border-slate-300 px-4 text-sm font-semibold">Filter</button></form>
        <a href="{{ route('core.contacts.export') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">Export CSV</a>
    </div>
    <section class="mt-4 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500"><tr><th class="px-5 py-3">Name</th><th class="px-5 py-3">Email</th><th class="px-5 py-3">Mobile</th><th class="px-5 py-3">Groups</th><th class="px-5 py-3">Status</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($contacts as $contact)<tr><td class="px-5 py-4 font-medium">{{ trim($contact->first_name.' '.$contact->last_name) }}</td><td class="px-5 py-4">{{ $contact->email }}</td><td class="px-5 py-4">{{ $contact->mobile_number }}</td><td class="px-5 py-4">{{ $contact->groups->pluck('name')->join(', ') }}</td><td class="px-5 py-4">{{ ucfirst($contact->email_status) }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No contacts yet.</td></tr>@endforelse</tbody></table></div>
    </section>
    <div class="mt-5">{{ $contacts->links() }}</div>
</x-layouts.admin>
