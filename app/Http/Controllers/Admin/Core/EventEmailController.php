<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\EventConfirmationEmailRequest;
use App\Models\Event;
use App\Services\AuditLogger;
use App\Services\Core\EventConfirmationEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventEmailController extends Controller
{
    public function edit(Event $event, EventConfirmationEmailService $service): View
    {
        return view('admin.core.event-emails.edit', [
            'event' => $event,
            'template' => $service->templateFor($event),
            'placeholderGroups' => $service->placeholderGroups($event),
            'preview' => $service->preview($event),
            'recentEmailLogs' => $event->emailLogs()->latest()->limit(8)->get(),
        ]);
    }

    public function update(EventConfirmationEmailRequest $request, Event $event, EventConfirmationEmailService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $template = $service->saveTemplate($event, $request->validated(), $request->user()->id);
        $auditLogger->record('core.email.confirmation.update', "Updated confirmation email for {$event->title}.", $template);

        return redirect()->route('core.events.email.edit', $event)->with('status', 'Confirmation email template saved.');
    }

    public function preview(Event $event, EventConfirmationEmailService $service): View
    {
        return view('admin.core.event-emails.preview', [
            'event' => $event,
            'preview' => $service->preview($event),
        ]);
    }

    public function sendTest(Event $event, EventConfirmationEmailService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $log = $service->sendTest($event);
        $auditLogger->record('core.email.confirmation.test', "Sent confirmation test email for {$event->title}.", $log);

        return redirect()->route('core.events.email.edit', $event)->with('status', $log->status === 'sent' ? 'Test confirmation email sent.' : 'Test email failed. Check email logs.');
    }
}
