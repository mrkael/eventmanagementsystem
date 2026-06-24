<x-layouts.admin title="Agenda" heading="Agenda" eyebrow="Event Details">
    <x-ui.page-header
        eyebrow="Agenda"
        title="{{ $event->title }}"
        description="Create agendas and manage event sessions with ticket eligibility for future session check-in."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.show', $event) }}" class="ds-button-secondary">Event Details</a>
            <a href="{{ route('core.events.agendas.create', $event) }}" class="ds-button-primary"><x-ui.icon name="plus" class="size-4" /> Add Agenda</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'agenda'])

    <x-ui.card padding="p-0" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Agenda Title</th>
                        <th class="px-5 py-4">Total Sessions</th>
                        <th class="px-5 py-4">Created Date</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($agendas as $agenda)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="font-black text-slate-950">{{ $agenda->title }}</p>
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-700">{{ number_format($agenda->sessions_count) }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $agenda->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('core.events.agendas.show', [$event, $agenda]) }}" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-white">Manage Sessions</a>
                                    <a href="{{ route('core.events.agendas.edit', [$event, $agenda]) }}" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-white">Edit</a>
                                    <form method="POST" action="{{ route('core.events.agendas.destroy', [$event, $agenda]) }}" onsubmit="return confirm('Delete this agenda and its sessions?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-full border border-red-200 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10">
                                <x-ui.empty-state icon="calendar" title="No agenda yet" description="Create an agenda first, then add sessions and assign eligible tickets." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-6">{{ $agendas->links() }}</div>
</x-layouts.admin>
