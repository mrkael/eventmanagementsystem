<?php

namespace App\Http\Controllers\Admin\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\PageBuilderRequest;
use App\Models\Event;
use App\Services\AuditLogger;
use App\Services\Events\PageBuilderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventPageBuilderController extends Controller
{
    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        $version = $event->pageVersions()->latest('version')->first();

        return view('admin.events.builder', [
            'event' => $event,
            'sections' => $version?->sections ?: app(PageBuilderService::class)->defaultSections($event),
            'sectionTypes' => PageBuilderService::TYPES,
        ]);
    }

    public function update(PageBuilderRequest $request, Event $event, PageBuilderService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $version = $service->saveDraft($event, $service->normalize($request->page_sections));
        $auditLogger->record('events.page_builder.save', "Saved page builder draft for {$event->title}.", $event, [], ['version' => $version->version]);

        return redirect()->route('admin.events.builder.edit', $event)->with('status', 'Event page draft saved.');
    }

    public function publish(Event $event, PageBuilderService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('publish', $event);
        $version = $service->publish($event, request()->user());
        $auditLogger->record('events.page_builder.publish', "Published event page for {$event->title}.", $event, [], ['version' => $version->version]);

        return back()->with('status', 'Event page published successfully.');
    }

    public function preview(Event $event): View
    {
        $this->authorize('view', $event);
        $version = $event->pageVersions()->latest('version')->first();

        return view('public.events.show', [
            'event' => $event->load('venue', 'sessions', 'documents'),
            'sections' => $version?->sections ?: app(PageBuilderService::class)->defaultSections($event),
            'preview' => true,
        ]);
    }
}
