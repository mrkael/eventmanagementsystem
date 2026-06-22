<?php

namespace App\Http\Controllers\Admin\EventSetup;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventTypeRequest;
use App\Models\EventType;
use App\Services\AuditLogger;
use App\Services\EventSetup\EventMasterUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventTypeController extends Controller
{
    public function index(Request $request): View
    {
        $types = EventType::query()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', "%{$request->search}%")->orWhere('slug', 'like', "%{$request->search}%"))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->status === 'active'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.event-setup.types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.event-setup.types.create');
    }

    public function store(EventTypeRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $type = EventType::create($this->payload($request));
        $auditLogger->record('event_types.create', "Created event type {$type->name}.", $type, [], $type->toArray());

        return redirect()->route('admin.event-types.index')->with('status', 'Event type created successfully.');
    }

    public function show(EventType $eventType): View
    {
        return view('admin.event-setup.types.show', ['type' => $eventType]);
    }

    public function edit(EventType $eventType): View
    {
        return view('admin.event-setup.types.edit', ['type' => $eventType]);
    }

    public function update(EventTypeRequest $request, EventType $eventType, AuditLogger $auditLogger): RedirectResponse
    {
        $oldValues = $eventType->toArray();
        $eventType->update($this->payload($request));
        $auditLogger->record('event_types.update', "Updated event type {$eventType->name}.", $eventType, $oldValues, $eventType->fresh()->toArray());

        return redirect()->route('admin.event-types.index')->with('status', 'Event type updated successfully.');
    }

    public function destroy(EventType $eventType, EventMasterUsageService $usageService, AuditLogger $auditLogger): RedirectResponse
    {
        if ($usageService->isUsed($eventType)) {
            return back()->withErrors(['type' => 'This type is already used by an event and cannot be deleted.']);
        }

        $oldValues = $eventType->toArray();
        $eventType->delete();
        $auditLogger->record('event_types.delete', "Deleted event type {$eventType->name}.", $eventType, $oldValues);

        return redirect()->route('admin.event-types.index')->with('status', 'Event type deleted successfully.');
    }

    private function payload(EventTypeRequest $request): array
    {
        $data = $request->validated();

        return [
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'requires_approval' => $request->boolean('requires_approval'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order'] ?? 0,
        ];
    }
}
