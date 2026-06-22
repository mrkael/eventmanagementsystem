<?php

namespace App\Http\Controllers\Admin\EventSetup;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventStatusRequest;
use App\Models\EventStatus;
use App\Services\AuditLogger;
use App\Services\EventSetup\EventMasterUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventStatusController extends Controller
{
    public function index(Request $request): View
    {
        $statuses = EventStatus::query()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', "%{$request->search}%")->orWhere('key', 'like', "%{$request->search}%"))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->status === 'active'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.event-setup.statuses.index', compact('statuses'));
    }

    public function create(): View
    {
        return view('admin.event-setup.statuses.create');
    }

    public function store(EventStatusRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $status = EventStatus::create($this->payload($request));
        $auditLogger->record('event_statuses.create', "Created event status {$status->name}.", $status, [], $status->toArray());

        return redirect()->route('admin.event-statuses.index')->with('status', 'Event status created successfully.');
    }

    public function show(EventStatus $eventStatus): View
    {
        return view('admin.event-setup.statuses.show', ['status' => $eventStatus]);
    }

    public function edit(EventStatus $eventStatus): View
    {
        return view('admin.event-setup.statuses.edit', ['status' => $eventStatus]);
    }

    public function update(EventStatusRequest $request, EventStatus $eventStatus, AuditLogger $auditLogger): RedirectResponse
    {
        $oldValues = $eventStatus->toArray();
        $eventStatus->update($this->payload($request));
        $auditLogger->record('event_statuses.update', "Updated event status {$eventStatus->name}.", $eventStatus, $oldValues, $eventStatus->fresh()->toArray());

        return redirect()->route('admin.event-statuses.index')->with('status', 'Event status updated successfully.');
    }

    public function destroy(EventStatus $eventStatus, EventMasterUsageService $usageService, AuditLogger $auditLogger): RedirectResponse
    {
        if ($usageService->isUsed($eventStatus)) {
            return back()->withErrors(['status' => 'This status is already used by an event and cannot be deleted.']);
        }

        $oldValues = $eventStatus->toArray();
        $eventStatus->delete();
        $auditLogger->record('event_statuses.delete', "Deleted event status {$eventStatus->name}.", $eventStatus, $oldValues);

        return redirect()->route('admin.event-statuses.index')->with('status', 'Event status deleted successfully.');
    }

    private function payload(EventStatusRequest $request): array
    {
        $data = $request->validated();

        return [
            'name' => $data['name'],
            'key' => $data['key'],
            'color' => $data['color'],
            'is_terminal' => $request->boolean('is_terminal'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order'] ?? 0,
        ];
    }
}
