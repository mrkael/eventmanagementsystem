<x-layouts.admin title="Edit Event" heading="Edit Event" subheading="{{ $event->title }}">
    <form method="POST" action="{{ route('core.events.update', $event) }}" enctype="multipart/form-data" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @csrf @method('PUT')
        @include('admin.core.events._form', ['event' => $event])
        <div class="mt-6 flex justify-end"><button class="min-h-11 rounded-lg bg-emerald-700 px-5 text-sm font-semibold text-white">Update event</button></div>
    </form>
</x-layouts.admin>
