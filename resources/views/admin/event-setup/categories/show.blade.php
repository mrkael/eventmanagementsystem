<x-layouts.admin title="Event Category Detail" heading="{{ $category->name }}" subheading="Category record">
    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <dl class="grid gap-5 sm:grid-cols-2"><div><dt class="text-sm text-slate-500">Slug</dt><dd class="font-mono text-sm">{{ $category->slug }}</dd></div><div><dt class="text-sm text-slate-500">Status</dt><dd>{{ $category->is_active ? 'Active' : 'Inactive' }}</dd></div><div class="sm:col-span-2"><dt class="text-sm text-slate-500">Description</dt><dd>{{ $category->description ?: 'No description provided.' }}</dd></div></dl>
        <div class="mt-6 flex gap-3"><a href="{{ route('admin.event-categories.edit', $category) }}" class="btn btn-primary btn-md">Edit</a><a href="{{ route('admin.event-categories.index') }}" class="btn btn-outline-primary btn-md">Back</a></div>
    </section>
</x-layouts.admin>
