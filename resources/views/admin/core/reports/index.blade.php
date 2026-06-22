<x-layouts.admin title="Event Report" heading="Event Report" subheading="{{ $event->title }}">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($overview as $label => $value)
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm capitalize text-slate-500">{{ str_replace('_', ' ', $label) }}</p><p class="mt-2 text-3xl font-bold">{{ number_format($value) }}</p></div>
        @endforeach
    </div>
    <form method="POST" action="{{ route('core.events.reports.store', $event) }}" class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        <h2 class="mb-4 font-semibold">Generate custom report</h2>
        <div class="grid gap-4 lg:grid-cols-3">
            <input name="name" required placeholder="Report name" class="rounded-lg border-slate-300">
            <select name="module" class="rounded-lg border-slate-300" data-module><option value="tickets">Ticket Module</option><option value="attendees">Attendees Module</option></select>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_on_overview" value="1"> Show on overview</label>
        </div>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @foreach($columns as $module => $items)
                <div data-columns="{{ $module }}" class="rounded-lg border border-slate-200 p-4">
                    <p class="mb-3 font-semibold capitalize">{{ $module }} columns</p>
                    <div class="grid gap-2 sm:grid-cols-2">@foreach($items as $column)<label class="flex gap-2 text-sm"><input type="checkbox" name="selected_columns[]" value="{{ $column }}"> {{ str_replace('_', ' ', $column) }}</label>@endforeach</div>
                </div>
            @endforeach
        </div>
        <button class="mt-4 min-h-11 rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white">Save report</button>
    </form>
    <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b px-5 py-4"><h2 class="font-semibold">Saved reports</h2></div>
        <div class="divide-y divide-slate-100">@forelse($reports as $report)<div class="flex items-center justify-between px-5 py-4 text-sm"><div><p class="font-semibold">{{ $report->name }}</p><p class="text-slate-500">{{ ucfirst($report->module) }} · {{ implode(', ', $report->selected_columns) }}</p></div><a href="{{ route('core.events.reports.export', [$event, $report]) }}" class="rounded-lg border border-slate-300 px-3 py-2 font-semibold">Export</a></div>@empty<p class="px-5 py-6 text-slate-500">No saved reports yet.</p>@endforelse</div>
    </section>
</x-layouts.admin>
