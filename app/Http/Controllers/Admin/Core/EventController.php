<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\CoreEventRequest;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventStatus;
use App\Models\EventType;
use App\Models\OrganiserProfile;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isPlatformAdmin = $user->isPlatformAdmin();
        $organiserProfile = $user->organiserProfile;

        $events = Event::query()
            ->with('organiserProfile')
            ->withCount('tickets')
            ->when(! $isPlatformAdmin, fn ($query) => $query->where('organiser_profile_id', $organiserProfile?->id ?? 0))
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', "%{$request->search}%"))
            ->when($isPlatformAdmin && $request->filled('organiser_profile_id'), fn ($query) => $query->where('organiser_profile_id', $request->organiser_profile_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status_key', $request->status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.core.events.index', [
            'events' => $events,
            'organiserProfiles' => $isPlatformAdmin ? OrganiserProfile::orderBy('name')->get() : collect([$organiserProfile])->filter(),
            'statuses' => ['draft', 'submitted', 'published'],
            'isPlatformAdmin' => $isPlatformAdmin,
        ]);
    }

    public function create(Request $request): View
    {
        $isPlatformAdmin = $request->user()->isPlatformAdmin();

        return view('admin.core.events.create', [
            'organiserProfiles' => $this->availableOrganiserProfiles($request),
            'isPlatformAdmin' => $isPlatformAdmin,
            'ownOrganiserProfile' => $request->user()->organiserProfile,
        ]);
    }

    public function store(CoreEventRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $event = Event::create($this->payload($request));
        $auditLogger->record('events.create', "Created event {$event->title}.", $event);

        return redirect()->route('core.events.show', $event)->with('status', 'Event saved.');
    }

    public function show(Event $event): View
    {
        return view('admin.core.events.show', [
            'event' => $event->loadCount('tickets', 'coreRegistrations', 'sessions')->load('publishedPage'),
        ]);
    }

    public function edit(Event $event): View
    {
        $isPlatformAdmin = request()->user()->isPlatformAdmin();

        return view('admin.core.events.edit', [
            'event' => $event,
            'organiserProfiles' => $this->availableOrganiserProfiles(request()),
            'isPlatformAdmin' => $isPlatformAdmin,
            'ownOrganiserProfile' => request()->user()->organiserProfile,
        ]);
    }

    public function update(CoreEventRequest $request, Event $event, AuditLogger $auditLogger): RedirectResponse
    {
        $event->update($this->payload($request));
        $auditLogger->record('events.update', "Updated event {$event->title}.", $event);

        return redirect()->route('core.events.show', $event)->with('status', 'Event updated.');
    }

    private function payload(CoreEventRequest $request): array
    {
        $category = EventCategory::firstOrCreate(['slug' => 'core'], ['name' => 'Core Events', 'is_active' => true]);
        $type = EventType::firstOrCreate(['slug' => 'standard'], ['name' => 'Standard', 'is_active' => true]);
        $status = EventStatus::firstOrCreate(['key' => 'draft'], ['name' => 'Draft', 'is_active' => true]);

        $organiserProfileId = $request->user()->isPlatformAdmin()
            ? $request->integer('organiser_profile_id')
            : $request->user()->organiserProfile?->id;

        return [
            'organizer_id' => $request->user()->id,
            'organiser_profile_id' => $organiserProfileId,
            'event_category_id' => $category->id,
            'event_type_id' => $type->id,
            'event_status_id' => $status->id,
            'title' => $request->title,
            'slug' => $request->slug,
            'custom_url' => $request->filled('custom_url') ? $request->custom_url : null,
            'summary' => str($request->description ?? '')->limit(500, ''),
            'description' => $request->description,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'location' => $request->location,
            'allow_duplicate_email' => $request->boolean('allow_duplicate_email_registration'),
            'status_key' => $request->status_key,
            'is_public' => $request->status_key === 'published',
            'is_registration_enabled' => true,
            'registration_opens_at' => $request->registration_opens_at,
            'registration_closes_at' => $request->registration_closes_at,
            'updated_by' => $request->user()->id,
            ...($request->isMethod('post') ? ['created_by' => $request->user()->id] : []),
        ];
    }

    private function availableOrganiserProfiles(Request $request)
    {
        if ($request->user()->isPlatformAdmin()) {
            return OrganiserProfile::where('status', 'active')->orderBy('name')->get();
        }

        return collect([$request->user()->organiserProfile])->filter();
    }
}
