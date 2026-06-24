<x-layouts.admin title="Forms" heading="Forms" eyebrow="Event Details">
    <x-ui.page-header
        eyebrow="Registration forms"
        title="{{ $event->title }}"
        description="Create registration forms and assign each form to one or more tickets for this event."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.show', $event) }}" class="ds-button-secondary">Event Details</a>
            <a href="{{ route('core.events.forms.create', $event) }}" class="ds-button-primary"><x-ui.icon name="plus" class="size-4" /> Add New</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'forms'])

    <x-ui.card padding="p-0" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Form Title</th>
                        <th class="px-5 py-4">Assigned Ticket(s)</th>
                        <th class="px-5 py-4">Total Questions</th>
                        <th class="px-5 py-4">Created Date</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($forms as $form)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-950">{{ $form->title }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                @forelse($form->tickets as $ticket)
                                    <span class="mb-1 mr-1 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $ticket->name }}</span>
                                @empty
                                    <span class="text-amber-700">No tickets assigned</span>
                                @endforelse
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-700">{{ number_format($form->fields_count) }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $form->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('core.events.forms.preview', [$event, $form]) }}" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-white">Preview</a>
                                    <a href="{{ route('core.events.forms.edit', [$event, $form]) }}" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-white">Edit</a>
                                    <form method="POST" action="{{ route('core.events.forms.destroy', [$event, $form]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-full border border-red-200 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10">
                                <x-ui.empty-state icon="editor" title="No forms yet" description="Create a registration form and assign it to one or more tickets before future event registration opens." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-6">{{ $forms->links() }}</div>
</x-layouts.admin>
