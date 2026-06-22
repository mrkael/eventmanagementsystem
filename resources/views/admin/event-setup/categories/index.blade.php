<x-layouts.admin title="Event Categories" heading="Event Categories" subheading="Classify events for filtering and reporting">
    <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-5 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" class="flex flex-1 flex-col gap-3 sm:flex-row">
                <input name="search" value="{{ request('search') }}" placeholder="Search category" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                <select name="status" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                    <option value="">All statuses</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button class="min-h-11 rounded-lg border border-slate-300 px-4 text-sm font-semibold hover:bg-slate-50">Filter</button>
            </form>
            <a href="{{ route('admin.event-categories.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800">New category</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Category</th><th class="px-5 py-3">Slug</th><th class="px-5 py-3">Order</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($categories as $category)
                        <tr>
                            <td class="px-5 py-4"><a href="{{ route('admin.event-categories.show', $category) }}" class="font-semibold text-slate-900 hover:text-emerald-700">{{ $category->name }}</a><p class="mt-1 text-slate-500">{{ $category->description }}</p></td>
                            <td class="px-5 py-4 font-mono text-xs">{{ $category->slug }}</td>
                            <td class="px-5 py-4">{{ $category->sort_order }}</td>
                            <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $category->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="px-5 py-4"><div class="flex justify-end gap-2"><a href="{{ route('admin.event-categories.edit', $category) }}" class="rounded-lg border border-slate-300 px-3 py-2 font-semibold hover:bg-slate-50">Edit</a><form method="POST" action="{{ route('admin.event-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-3 py-2 font-semibold text-red-700 hover:bg-red-50">Delete</button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-5">{{ $categories->links() }}</div>
    </section>
</x-layouts.admin>
