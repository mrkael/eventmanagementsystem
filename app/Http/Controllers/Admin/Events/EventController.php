<?php

namespace App\Http\Controllers\Admin\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\EventRequest;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventConfiguration;
use App\Models\EventDocument;
use App\Models\EventStatus;
use App\Models\EventType;
use App\Models\Venue;
use App\Services\AuditLogger;
use App\Services\Events\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $events = Event::query()
            ->with(['organizer', 'category', 'type', 'venue', 'status'])
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', "%{$request->search}%")->orWhere('slug', 'like', "%{$request->search}%"))
            ->when($request->filled('status_key'), fn ($query) => $query->where('status_key', $request->status_key))
            ->when($request->filled('event_category_id'), fn ($query) => $query->where('event_category_id', $request->event_category_id))
            ->when($request->filled('venue_id'), fn ($query) => $query->where('venue_id', $request->venue_id))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('starts_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('starts_at', '<=', $request->date_to))
            ->latest('starts_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.events.index', [
            'events' => $events,
            'categories' => EventCategory::orderBy('name')->get(),
            'venues' => Venue::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Event::class);

        return view('admin.events.create', $this->formData());
    }

    public function store(EventRequest $request, EventService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();
        $event = $service->save($data, $request->user());
        if ($request->boolean('publish_now')) {
            $service->publish($event, $request->user());
        }
        $auditLogger->record('events.create', "Created event {$event->title}.", $event, [], $event->toArray());

        return redirect()->route('admin.events.show', $event)->with('status', $request->boolean('publish_now') ? 'Event saved and published.' : 'Event saved as draft.');
    }

    public function show(Event $event): View
    {
        $this->authorize('view', $event);

        return view('admin.events.show', ['event' => $event->load(['organizer', 'category', 'type', 'venue', 'status', 'configuration', 'sessions.venue', 'documents', 'publishedPageVersion'])]);
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        return view('admin.events.edit', ['event' => $event->load(['sessions', 'pageVersions'])] + $this->formData());
    }

    public function update(EventRequest $request, Event $event, EventService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();
        $oldValues = $event->load('sessions', 'documents')->toArray();
        $event = $service->save($data, $request->user(), $event);
        if ($request->boolean('publish_now')) {
            $service->publish($event, $request->user());
        }
        $auditLogger->record('events.update', "Updated event {$event->title}.", $event, $oldValues, $event->load('sessions', 'documents')->toArray());

        return redirect()->route('admin.events.show', $event)->with('status', $request->boolean('publish_now') ? 'Event updated and published.' : 'Event updated successfully.');
    }

    public function destroy(Event $event, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('delete', $event);
        $oldValues = $event->toArray();
        $event->delete();
        $auditLogger->record('events.delete', "Deleted event {$event->title}.", $event, $oldValues);

        return redirect()->route('admin.events.index')->with('status', 'Event deleted successfully.');
    }

    public function submit(Event $event, EventService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('submit', $event);
        $service->submit($event);
        $auditLogger->record('events.submit', "Submitted event {$event->title}.", $event);

        return back()->with('status', 'Event submitted successfully.');
    }

    public function publish(Event $event, EventService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('publish', $event);
        $service->publish($event, request()->user());
        $auditLogger->record('events.publish', "Published event {$event->title}.", $event);

        return back()->with('status', 'Event published successfully.');
    }

    public function destroyDocument(Event $event, EventDocument $document, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('update', $event);
        abort_unless($document->event_id === $event->id, 404);
        Storage::disk('public')->delete($document->path);
        $document->delete();
        $auditLogger->record('events.documents.delete', "Deleted document from {$event->title}.", $event);

        return back()->with('status', 'Document deleted successfully.');
    }

    private function formData(): array
    {
        return [
            'categories' => EventCategory::where('is_active', true)->orderBy('name')->get(),
            'types' => EventType::where('is_active', true)->orderBy('name')->get(),
            'venues' => Venue::orderBy('name')->get(),
            'statuses' => EventStatus::where('is_active', true)->orderBy('sort_order')->get(),
            'configurations' => EventConfiguration::orderByDesc('is_default')->orderBy('name')->get(),
        ];
    }
}
