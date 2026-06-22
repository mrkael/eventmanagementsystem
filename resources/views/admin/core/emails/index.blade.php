<x-layouts.admin title="Emails" heading="Emails" subheading="Confirmation, EDM, invite templates, and sending">
    <div class="grid gap-6 xl:grid-cols-2">
        <form method="POST" action="{{ route('core.emails.templates.store') }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            <h2 class="mb-4 font-semibold">Create email template</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <select name="event_id" class="rounded-lg border-slate-300"><option value="">Global template</option>@foreach($events as $event)<option value="{{ $event->id }}">{{ $event->title }}</option>@endforeach</select>
                <select name="type" class="rounded-lg border-slate-300"><option value="confirmation">Confirmation</option><option value="pay_later">Pay later</option><option value="edm">EDM</option><option value="invite">Invite</option></select>
                <input name="name" required placeholder="Template name" class="rounded-lg border-slate-300">
                <input name="subject" required placeholder="Subject line" class="rounded-lg border-slate-300">
                <input name="preheader" placeholder="Preheader line" class="rounded-lg border-slate-300 md:col-span-2">
                <textarea name="body" required rows="8" class="rounded-lg border-slate-300 md:col-span-2">Hello {{ '{{ participant_name }}' }},&#10;&#10;Thank you for registering for {{ '{{ event_name }}' }}.&#10;Reference: {{ '{{ registration_reference }}' }}&#10;{{ '{{ qr_code }}' }}</textarea>
            </div>
            <button class="mt-4 min-h-11 rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white">Save template</button>
        </form>
        <form method="POST" action="{{ route('core.emails.send') }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            <h2 class="mb-4 font-semibold">Send email</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <select name="email_template_id" required class="rounded-lg border-slate-300"><option value="">Email design</option>@foreach($templates as $template)<option value="{{ $template->id }}">{{ $template->name ?? $template->subject }}</option>@endforeach</select>
                <select name="type" class="rounded-lg border-slate-300"><option value="edm">EDM</option><option value="invite">Invite</option></select>
                <input name="sender_name" required placeholder="Sender name" class="rounded-lg border-slate-300">
                <input type="email" name="sender_email" required placeholder="Sender email" class="rounded-lg border-slate-300">
                <input name="subject" required placeholder="Subject line" class="rounded-lg border-slate-300">
                <input name="preheader" placeholder="Preheader line" class="rounded-lg border-slate-300">
                <select name="event_id" class="rounded-lg border-slate-300"><option value="">No event</option>@foreach($events as $event)<option value="{{ $event->id }}">{{ $event->title }}</option>@endforeach</select>
                <select name="group_id" class="rounded-lg border-slate-300"><option value="">All contacts</option>@foreach($groups as $group)<option value="{{ $group->id }}">{{ $group->name }} ({{ $group->contacts_count }})</option>@endforeach</select>
                <input type="datetime-local" name="scheduled_at" class="rounded-lg border-slate-300">
                <select name="mode" class="rounded-lg border-slate-300"><option value="test">Test Email</option><option value="send">Send Now</option><option value="schedule">Schedule</option></select>
            </div>
            <button class="mt-4 min-h-11 rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white">Save campaign</button>
        </form>
    </div>
    <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b px-5 py-4"><h2 class="font-semibold">Recent campaigns</h2></div>
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500"><tr><th class="px-5 py-3">Subject</th><th class="px-5 py-3">Type</th><th class="px-5 py-3">Recipients</th><th class="px-5 py-3">Status</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($campaigns as $campaign)<tr><td class="px-5 py-4 font-medium">{{ $campaign->subject }}</td><td class="px-5 py-4">{{ strtoupper($campaign->type) }}</td><td class="px-5 py-4">{{ $campaign->recipient_count }}</td><td class="px-5 py-4">{{ ucfirst($campaign->status) }}</td></tr>@empty<tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">No email campaigns yet.</td></tr>@endforelse</tbody></table></div>
    </section>
</x-layouts.admin>
