<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\Event;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailController extends Controller
{
    public function index(): View
    {
        return view('admin.core.emails.index', [
            'events' => Event::orderByDesc('starts_at')->get(),
            'templates' => EmailTemplate::with('event')->latest()->paginate(10),
            'campaigns' => EmailCampaign::with('event', 'template')->latest()->limit(10)->get(),
            'groups' => ContactGroup::withCount('contacts')->orderBy('name')->get(),
        ]);
    }

    public function storeTemplate(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'event_id' => ['nullable', 'exists:events,id'],
            'type' => ['required', 'in:confirmation,pay_later,edm,invite'],
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);
        $template = EmailTemplate::create($data + ['is_active' => true]);
        $auditLogger->record('eevee.email.template.create', "Created email template {$template->name}.", $template);

        return back()->with('status', 'Email template saved.');
    }

    public function send(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'event_id' => ['nullable', 'exists:events,id'],
            'email_template_id' => ['required', 'exists:email_templates,id'],
            'type' => ['required', 'in:edm,invite'],
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_email' => ['required', 'email:rfc', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'group_id' => ['nullable', 'exists:contact_groups,id'],
            'scheduled_at' => ['nullable', 'date'],
            'mode' => ['required', 'in:test,send,schedule'],
        ]);

        $recipientCount = Contact::query()
            ->when($request->filled('group_id'), fn ($query) => $query->whereHas('groups', fn ($groups) => $groups->whereKey($request->group_id)))
            ->whereNotNull('email')
            ->count();

        $campaign = EmailCampaign::create([
            'event_id' => $data['event_id'] ?? null,
            'email_template_id' => $data['email_template_id'],
            'created_by' => $request->user()->id,
            'type' => $data['type'],
            'sender_name' => $data['sender_name'],
            'sender_email' => $data['sender_email'],
            'subject' => $data['subject'],
            'preheader' => $data['preheader'] ?? null,
            'recipient_filters' => ['group_id' => $data['group_id'] ?? null],
            'recipient_count' => $data['mode'] === 'test' ? 1 : $recipientCount,
            'status' => $data['mode'] === 'schedule' ? 'scheduled' : 'sent',
            'scheduled_at' => $data['mode'] === 'schedule' ? $data['scheduled_at'] : null,
            'sent_at' => $data['mode'] === 'send' ? now() : null,
        ]);
        $auditLogger->record('eevee.email.campaign', "Created {$campaign->type} campaign {$campaign->subject}.", $campaign);

        return back()->with('status', $data['mode'] === 'test' ? 'Test email logged.' : 'Email campaign saved.');
    }
}
