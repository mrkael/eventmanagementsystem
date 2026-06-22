<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\CoreEventRequest;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventStatus;
use App\Models\EventType;
use App\Services\AuditLogger;
use App\Services\Core\MicrositeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $events = Event::query()
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', "%{$request->search}%")->orWhere('custom_url', 'like', "%{$request->search}%"))
            ->orderByRaw('custom_url IS NULL')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.core.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('admin.core.events.create', [
            'organiserProfiles' => \App\Models\OrganiserProfile::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(CoreEventRequest $request, MicrositeService $microsite, AuditLogger $auditLogger): RedirectResponse
    {
        $event = Event::create($this->payload($request));
        $this->storeFiles($event, $request);
        $microsite->save($event, ['sections' => $microsite->defaultSections($event)]);
        $status = $event->status_key instanceof \BackedEnum ? $event->status_key->value : (string) $event->status_key;
        if ($status === 'published') {
            $microsite->publish($event);
        }
        $auditLogger->record('core.events.create', "Created event {$event->title}.", $event);

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
        return view('admin.core.events.edit', [
            'event' => $event,
            'organiserProfiles' => \App\Models\OrganiserProfile::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(CoreEventRequest $request, Event $event, MicrositeService $microsite, AuditLogger $auditLogger): RedirectResponse
    {
        $event->update($this->payload($request));
        $this->storeFiles($event, $request);
        if ($request->input('status_key') === 'published') {
            $microsite->publish($event);
        }
        $auditLogger->record('core.events.update', "Updated event {$event->title}.", $event);

        return redirect()->route('core.events.show', $event)->with('status', 'Event updated.');
    }

    private function payload(CoreEventRequest $request): array
    {
        $category = EventCategory::firstOrCreate(['slug' => 'core'], ['name' => 'Core Events', 'is_active' => true]);
        $type = EventType::firstOrCreate(['slug' => 'standard'], ['name' => 'Standard', 'is_active' => true]);
        $status = EventStatus::firstOrCreate(['key' => 'draft'], ['name' => 'Draft', 'is_active' => true]);

        return [
            'organizer_id' => $request->user()->id,
            'organiser_profile_id' => $request->organiser_profile_id,
            'event_category_id' => $category->id,
            'event_type_id' => $type->id,
            'event_status_id' => $status->id,
            'title' => $request->title,
            'slug' => $request->custom_url,
            'custom_url' => $request->custom_url,
            'summary' => $request->summary,
            'description' => $request->description,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'location' => $request->location,
            'capacity' => $request->capacity,
            'payment_tax_percentage' => $request->input('payment_tax_percentage', 0),
            'allow_promo_code' => $request->boolean('allow_promo_code'),
            'allow_duplicate_email' => $request->boolean('allow_duplicate_email'),
            'sender_name' => $request->sender_name,
            'sender_email' => $request->sender_email,
            'status_key' => $request->status_key,
            'is_public' => $request->status_key === 'published',
            'is_registration_enabled' => true,
            'brand_color' => $request->brand_color ?: '#047857',
            'registration_opens_at' => $request->registration_opens_at,
            'registration_closes_at' => $request->registration_closes_at,
        ];
    }

    private function storeFiles(Event $event, CoreEventRequest $request): void
    {
        $updates = [];
        if ($request->hasFile('logo')) {
            if ($event->logo_path) {
                Storage::disk('public')->delete($event->logo_path);
            }
            $updates['logo_path'] = $request->file('logo')->store('events/logos', 'public');
        }
        if ($request->hasFile('banner')) {
            if ($event->banner_path) {
                Storage::disk('public')->delete($event->banner_path);
            }
            $updates['banner_path'] = $request->file('banner')->store('events/banners', 'public');
        }
        if ($updates) {
            $event->update($updates);
        }
    }
}
