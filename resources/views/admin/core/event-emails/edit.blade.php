<x-layouts.admin title="Confirmation Email" heading="Confirmation Email" eyebrow="Event Details">
    <x-ui.page-header
        eyebrow="Email"
        title="{{ $event->title }}"
        description="Customize the confirmation email sent after successful event registration."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.show', $event) }}" class="ds-button-secondary">Event Details</a>
            <a href="{{ route('core.events.email.preview', $event) }}" target="_blank" class="ds-button-secondary">Preview Email</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'email'])

    <form id="send-test-email-form" method="POST" action="{{ route('core.events.email.test', $event) }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('core.events.email.update', $event) }}">
        @csrf
        @method('PUT')

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-6">
                <x-ui.card>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-950">Template Settings</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">Use placeholders to personalize confirmation email content.</p>
                        </div>
                        <label class="flex cursor-pointer items-center gap-3 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-bold text-slate-700">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active)) class="rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                            Active
                        </label>
                    </div>

                    <div class="mt-6 space-y-5">
                        <label class="block">
                            <span class="ds-label">Email Subject</span>
                            <input name="subject" value="{{ old('subject', $template->subject) }}" required class="ds-input mt-2">
                            @error('subject')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                        </label>

                        <label class="block">
                            <span class="ds-label">Header Content</span>
                            <textarea name="header_content" rows="3" class="ds-input mt-2 py-3">{{ old('header_content', $template->header_content) }}</textarea>
                            @error('header_content')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                        </label>

                        <label class="block">
                            <span class="ds-label">Body Content</span>
                            <textarea name="body_content" rows="14" required class="ds-input mt-2 py-3">{{ old('body_content', $template->body_content) }}</textarea>
                            @error('body_content')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                        </label>

                        <label class="block">
                            <span class="ds-label">Footer Content</span>
                            <textarea name="footer_content" rows="4" class="ds-input mt-2 py-3">{{ old('footer_content', $template->footer_content) }}</textarea>
                            @error('footer_content')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                        </label>
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="submit" class="ds-button-primary justify-center">Save Email Template</button>
                        <a href="{{ route('core.events.email.preview', $event) }}" target="_blank" class="ds-button-secondary justify-center">Preview Email</a>
                        <button type="submit" form="send-test-email-form" class="ds-button-secondary justify-center">Send Test Email</button>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        @if(config('event_management.confirmation_email_test_mode'))
                            Test mode is enabled. Registration emails are delivered to {{ config('event_management.confirmation_email_test_recipient') }} while participant email remains stored normally.
                        @else
                            Test mode is disabled. Registration emails are delivered to the participant email address.
                        @endif
                    </p>
                </x-ui.card>
            </div>

            <aside class="space-y-6 xl:sticky xl:top-24 xl:self-start">
                <x-ui.card>
                    <h2 class="text-xl font-semibold text-slate-950">SMTP Test Settings</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-bold text-slate-500">Mailer</span>
                            <span class="font-black text-slate-950">{{ config('mail.default') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-bold text-slate-500">Host</span>
                            <span class="text-right font-black text-slate-950">{{ config('mail.mailers.smtp.host') }}:{{ config('mail.mailers.smtp.port') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-bold text-slate-500">Recipient Mode</span>
                            <span class="font-black {{ config('event_management.confirmation_email_test_mode') ? 'text-amber-700' : 'text-emerald-700' }}">{{ config('event_management.confirmation_email_test_mode') ? 'Test override' : 'Participant email' }}</span>
                        </div>
                        <div>
                            <p class="font-bold text-slate-500">Current Test Recipient</p>
                            <p class="mt-1 break-all font-black text-slate-950">{{ config('event_management.confirmation_email_test_recipient') }}</p>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <h2 class="text-xl font-semibold text-slate-950">Placeholders</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Click any placeholder to copy it.</p>
                    <div class="mt-5 space-y-5">
                        @foreach($placeholderGroups as $group => $placeholders)
                            <div>
                                <p class="text-xs font-black uppercase text-slate-400">{{ $group }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @forelse($placeholders as $placeholder)
                                        <button type="button" data-copy-placeholder="{{ $placeholder }}" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">{{ $placeholder }}</button>
                                    @empty
                                        <span class="text-sm text-slate-500">Create form fields to show dynamic placeholders.</span>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <h2 class="text-xl font-semibold text-slate-950">Current Preview</h2>
                    <p class="mt-2 text-sm font-bold text-slate-600">{{ $preview['subject'] }}</p>
                    <div class="mt-4 rounded-[22px] border border-slate-200 bg-white p-4 text-sm leading-6 text-slate-600">
                        @if($preview['header'])
                            <div class="mb-3 text-lg font-bold text-slate-950">{!! $preview['header'] !!}</div>
                        @endif
                        <div>{!! $preview['body'] !!}</div>
                        @if($preview['footer'])
                            <div class="mt-4 border-t border-slate-200 pt-3 text-xs text-slate-500">{!! $preview['footer'] !!}</div>
                        @endif
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <h2 class="text-xl font-semibold text-slate-950">Recent Email Sends</h2>
                    <div class="mt-4 space-y-3">
                        @forelse($recentEmailLogs as $log)
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs font-black uppercase text-slate-500">{{ str($log->email_type)->headline() }}</p>
                                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $log->status === 'sent' ? 'bg-emerald-50 text-emerald-700' : ($log->status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600') }}">{{ str($log->status)->headline() }}</span>
                                </div>
                                <p class="mt-2 break-all text-sm font-bold text-slate-950">{{ $log->recipient_email }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $log->created_at->format('d M Y, H:i') }}</p>
                                @if($log->error_message)
                                    <p class="mt-2 rounded-xl bg-red-50 p-3 text-xs font-semibold text-red-700">{{ $log->error_message }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="rounded-2xl bg-slate-50 p-4 text-sm font-semibold text-slate-500">No confirmation email sends recorded yet.</p>
                        @endforelse
                    </div>
                </x-ui.card>
            </aside>
        </div>
    </form>

    <script>
        document.querySelectorAll('[data-copy-placeholder]').forEach((button) => {
            button.addEventListener('click', async () => {
                await navigator.clipboard.writeText(button.dataset.copyPlaceholder);
                button.textContent = 'Copied';
                window.setTimeout(() => button.textContent = button.dataset.copyPlaceholder, 900);
            });
        });
    </script>
</x-layouts.admin>
