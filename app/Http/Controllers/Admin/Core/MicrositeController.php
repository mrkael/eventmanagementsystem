<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\AuditLogger;
use App\Services\Core\MicrositeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MicrositeController extends Controller
{
    public function edit(Event $event, MicrositeService $service): View
    {
        $page = $event->pages()->with('sections')->where('status', 'draft')->latest()->first()
            ?? $service->save($event, ['sections' => $service->defaultSections($event)]);

        return view('admin.core.microsite.edit', [
            'event' => $event,
            'page' => $page,
            'sectionTypes' => MicrositeService::SECTION_TYPES,
            'visibleTickets' => $service->activeVisibleTickets($event),
        ]);
    }

    public function preview(Event $event, MicrositeService $service): View
    {
        $page = $event->pages()->with('sections')->where('status', 'draft')->latest()->first()
            ?? $service->save($event, ['sections' => $service->defaultSections($event)]);

        return view('public.core.events.show', [
            'event' => $event,
            'sections' => $page->sections,
            'tickets' => $service->activeVisibleTickets($event),
            'isPreview' => true,
        ]);
    }

    public function update(Request $request, Event $event, MicrositeService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate(['template' => ['required', 'string', 'max:80'], 'sections' => ['required', 'json']]);
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

    public function upload(Request $request, Event $event): array
    {
        $data = $request->validate([
            'files' => ['required'],
            'files.*' => ['image', 'max:4096'],
        ]);

        $files = collect($request->file('files', []))->flatten()->filter();
        $paths = $files->map(function ($file) use ($event) {
            $name = Str::uuid().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs("event-sites/{$event->id}", $name, 'public');

            return Storage::disk('public')->url($path);
        })->values();

        return ['data' => $paths, 'uploaded' => $paths->isNotEmpty(), 'errors' => []];
    }
}
