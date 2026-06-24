<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\OrganiserProfileRequest;
use App\Models\OrganiserProfile;
use App\Services\AuditLogger;
use App\Services\Core\OrganiserProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganiserProfileController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $request->input('sort') === 'name' ? 'name' : 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $profiles = OrganiserProfile::query()
            ->with('creator')
            ->withCount('events')
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->orderBy($sort, $direction)
            ->paginate(12)
            ->withQueryString();

        return view('admin.core.organisers.index', compact('profiles', 'sort', 'direction'));
    }

    public function create(): View
    {
        return view('admin.core.organisers.create');
    }

    public function store(OrganiserProfileRequest $request, OrganiserProfileService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $profile = $service->create($request->validated(), $request->user()->id);
        $auditLogger->record('organisers.create', "Created organiser profile {$profile->name}.", $profile);

        return redirect()->route('core.organisers.show', $profile)->with('status', 'Organiser profile created.');
    }

    public function show(OrganiserProfile $organiser): View
    {
        return view('admin.core.organisers.show', [
            'profile' => $organiser->load('creator', 'updater')->loadCount('events'),
        ]);
    }

    public function edit(OrganiserProfile $organiser): View
    {
        return view('admin.core.organisers.edit', [
            'profile' => $organiser,
        ]);
    }

    public function update(OrganiserProfileRequest $request, OrganiserProfile $organiser, OrganiserProfileService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $oldValues = $organiser->only(['name', 'email', 'phone', 'website', 'address', 'description', 'logo_path', 'status']);
        $profile = $service->update($organiser, $request->validated(), $request->user()->id);
        $auditLogger->record('organisers.update', "Updated organiser profile {$profile->name}.", $profile, $oldValues, $profile->only(array_keys($oldValues)));

        return redirect()->route('core.organisers.show', $profile)->with('status', 'Organiser profile updated.');
    }

    public function destroy(OrganiserProfile $organiser, OrganiserProfileService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $auditLogger->record('organisers.delete', "Deleted organiser profile {$organiser->name}.", $organiser);
        $service->delete($organiser);

        return redirect()->route('core.organisers.index')->with('status', 'Organiser profile deleted.');
    }
}
