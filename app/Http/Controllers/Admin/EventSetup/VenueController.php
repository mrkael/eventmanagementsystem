<?php

namespace App\Http\Controllers\Admin\EventSetup;

use App\Enums\VenueStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\VenueRequest;
use App\Models\Venue;
use App\Services\AuditLogger;
use App\Services\EventSetup\EventMasterUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function index(Request $request): View
    {
        $venues = Venue::query()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', "%{$request->search}%")->orWhere('code', 'like', "%{$request->search}%")->orWhere('location', 'like', "%{$request->search}%"))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.event-setup.venues.index', [
            'venues' => $venues,
            'statuses' => VenueStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.event-setup.venues.create', ['statuses' => VenueStatus::cases()]);
    }

    public function store(VenueRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $venue = Venue::create($request->validated());
        $auditLogger->record('venues.create', "Created venue {$venue->name}.", $venue, [], $venue->toArray());

        return redirect()->route('admin.venues.index')->with('status', 'Venue created successfully.');
    }

    public function show(Venue $venue): View
    {
        return view('admin.event-setup.venues.show', ['venue' => $venue]);
    }

    public function edit(Venue $venue): View
    {
        return view('admin.event-setup.venues.edit', [
            'venue' => $venue,
            'statuses' => VenueStatus::cases(),
        ]);
    }

    public function update(VenueRequest $request, Venue $venue, AuditLogger $auditLogger): RedirectResponse
    {
        $oldValues = $venue->toArray();
        $venue->update($request->validated());
        $auditLogger->record('venues.update', "Updated venue {$venue->name}.", $venue, $oldValues, $venue->fresh()->toArray());

        return redirect()->route('admin.venues.index')->with('status', 'Venue updated successfully.');
    }

    public function destroy(Venue $venue, EventMasterUsageService $usageService, AuditLogger $auditLogger): RedirectResponse
    {
        if ($usageService->isUsed($venue)) {
            return back()->withErrors(['venue' => 'This venue is already used by an event and cannot be deleted.']);
        }

        $oldValues = $venue->toArray();
        $venue->delete();
        $auditLogger->record('venues.delete', "Deleted venue {$venue->name}.", $venue, $oldValues);

        return redirect()->route('admin.venues.index')->with('status', 'Venue deleted successfully.');
    }
}
