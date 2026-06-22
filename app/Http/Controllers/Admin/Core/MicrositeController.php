<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\AuditLogger;
use App\Services\Core\MicrositeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MicrositeController extends Controller
{
    public function edit(Event $event, MicrositeService $service): View
    {
        $page = $event->pages()->with('sections')->where('status', 'draft')->latest()->first()
            ?? $service->save($event, ['sections' => $service->defaultSections($event)]);

        return view('admin.core.microsite.edit', ['event' => $event, 'page' => $page, 'sectionTypes' => MicrositeService::SECTION_TYPES]);
    }

    public function update(Request $request, Event $event, MicrositeService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate(['template' => ['nullable', 'string', 'max:50'], 'sections' => ['required', 'json']]);
        $page = $service->save($event, ['template' => $data['template'] ?? 'default', 'sections' => $data['sections']]);
        $auditLogger->record('core.microsite.save', "Saved microsite for {$event->title}.", $page);

        return back()->with('status', 'Microsite draft saved.');
    }

    public function publish(Event $event, MicrositeService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $page = $service->publish($event);
        $auditLogger->record('core.microsite.publish', "Published microsite for {$event->title}.", $page);

        return back()->with('status', 'Microsite published.');
    }
}
