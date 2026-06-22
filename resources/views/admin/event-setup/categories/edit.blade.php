<x-layouts.admin title="Edit Event Category" heading="Edit Event Category" subheading="{{ $category->name }}">
    <form method="POST" action="{{ route('admin.event-categories.update', $category) }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @method('PUT')
        @include('admin.event-setup.categories._form', ['button' => 'Update category'])
    </form>
</x-layouts.admin>
