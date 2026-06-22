<x-layouts.admin title="Create Event" heading="Create Event" subheading="Setup, branding, dates, capacity, and public status">
    <form method="POST" action="{{ route('core.events.store') }}" enctype="multipart/form-data" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        @include('admin.core.events._form')
        <div class="mt-6 flex justify-end"><button class="min-h-11 rounded-lg bg-emerald-700 px-5 text-sm font-semibold text-white">Save event</button></div>
    </form>
</x-layouts.admin>
