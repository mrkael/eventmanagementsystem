<x-layouts.admin title="Attendees Management" heading="Attendees Management" eyebrow="Foundation">
    <x-ui.page-header
        eyebrow="Management shell"
        title="Attendee workspace"
        description="A professional attendee management UI foundation with search, filters, table layout, bulk action region, and detail panel."
    />

    <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">
        <div class="space-y-6">
            <x-ui.card>
                <div class="grid gap-3 lg:grid-cols-[1fr_auto_auto]">
                    <label class="relative">
                        <x-ui.icon name="search" class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                        <input class="ds-input pl-11" placeholder="Search attendee name, email, ticket, organization">
                    </label>
                    <button type="button" class="ds-button-secondary"><x-ui.icon name="filter" class="size-4" /> Filters</button>
                    <button type="button" class="ds-button-secondary">Bulk actions</button>
                </div>
            </x-ui.card>

            <x-ui.card padding="p-0" class="overflow-hidden">
                <div class="border-b border-slate-200/80 p-5">
                    <p class="text-sm font-bold text-slate-500">Attendee table</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                            <tr>
                                <th class="px-5 py-4"><input type="checkbox" class="rounded border-slate-300"></th>
                                <th class="px-5 py-4">Name</th>
                                <th class="px-5 py-4">Email</th>
                                <th class="px-5 py-4">Ticket</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Updated</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach(['Aisha Rahman', 'Daniel Lim', 'Sofia Tan'] as $name)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-5 py-4"><input type="checkbox" class="rounded border-slate-300"></td>
                                    <td class="px-5 py-4 font-bold text-slate-950">{{ $name }}</td>
                                    <td class="px-5 py-4 text-slate-500">{{ \Illuminate\Support\Str::slug($name, '.') }}@example.com</td>
                                    <td class="px-5 py-4">VIP Pass</td>
                                    <td class="px-5 py-4"><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">Prepared</span></td>
                                    <td class="px-5 py-4 text-slate-500">Just now</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            <x-ui.empty-state icon="users" title="Empty attendee state" description="This state will guide event teams when no attendees match the current filter." />
        </div>

        <x-ui.card class="h-max">
            <p class="text-sm font-bold text-slate-500">Detail panel region</p>
            <div class="mt-5 rounded-[22px] bg-slate-950 p-5 text-white">
                <p class="text-lg font-semibold">Selected attendee</p>
                <p class="mt-2 text-sm leading-6 text-white/60">This panel is reserved for profile details, registration answers, notes, and future timeline states.</p>
            </div>
            <div class="mt-5 space-y-3">
                <x-ui.skeleton class="h-4 w-full" />
                <x-ui.skeleton class="h-4 w-2/3" />
                <x-ui.skeleton class="h-24" />
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
