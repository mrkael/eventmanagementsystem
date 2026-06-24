<x-layouts.admin title="Emails" heading="Emails" eyebrow="Foundation">
    <x-ui.page-header
        eyebrow="Template system"
        title="Email templates"
        description="A modern template listing and Editor.js editing foundation prepared for future variables, dynamic placeholders, and delivery states."
    />

    <div class="grid gap-6 xl:grid-cols-[24rem_1fr]">
        <div class="space-y-4">
            <x-ui.card>
                <label class="relative">
                    <x-ui.icon name="search" class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                    <input class="ds-input pl-11" placeholder="Search templates">
                </label>
            </x-ui.card>

            @foreach(['Registration confirmation', 'Invitation email', 'Pay later instructions'] as $index => $template)
                <a href="#email-editor" class="ds-card ds-card-hover block p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase text-blue-600">Template {{ $index + 1 }}</p>
                            <h2 class="mt-2 font-semibold text-slate-950">{{ $template }}</h2>
                        </div>
                        <x-ui.icon name="mail" class="size-5 text-slate-400" />
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-500">Subject, preheader, layout, and placeholder regions.</p>
                </a>
            @endforeach
        </div>

        <x-ui.card id="email-editor">
            <div class="mb-5 flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-500">Email Template Editor</p>
                    <h2 class="mt-1 text-xl font-semibold">Registration confirmation</h2>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach(['participant_name', 'event_name', 'event_date', 'qr_code'] as $token)
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ '{{ '.$token.' }}' }}</span>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <label><span class="ds-label">Subject</span><input class="ds-input mt-2" value="Your registration is confirmed"></label>
                <label><span class="ds-label">Preheader</span><input class="ds-input mt-2" value="Your event ticket details are ready"></label>
            </div>

            <div class="mt-5 rounded-[22px] border border-slate-200 bg-white p-5">
                <div id="email-template-editor" data-editorjs='{"holder":"email-template-editor","placeholder":"Compose the future email template body...","data":{"blocks":[{"type":"paragraph","data":{"text":"Hello @{{ participant_name }}, your registration for @{{ event_name }} is confirmed."}}]}}' class="min-h-96"></div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
