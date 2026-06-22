<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Models\OrganiserProfile;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrganiserProfileController extends Controller
{
    public function index(): View
    {
        return view('admin.core.organisers.index', [
            'profiles' => OrganiserProfile::withCount('events', 'users')->latest()->paginate(12),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:80'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('organisers', 'public');
        }

        $profile = OrganiserProfile::create($data + ['created_by' => $request->user()->id, 'is_active' => true]);
        $profile->users()->sync($request->input('user_ids', []));
        $auditLogger->record('eevee.organisers.create', "Created organiser profile {$profile->name}.", $profile);

        return back()->with('status', 'Organiser profile saved.');
    }

    public function update(Request $request, OrganiserProfile $organiser, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:80'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        if ($request->hasFile('logo')) {
            if ($organiser->logo_path) {
                Storage::disk('public')->delete($organiser->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('organisers', 'public');
        }

        $organiser->update($data + ['is_active' => $request->boolean('is_active')]);
        $organiser->users()->sync($request->input('user_ids', []));
        $auditLogger->record('eevee.organisers.update', "Updated organiser profile {$organiser->name}.", $organiser);

        return back()->with('status', 'Organiser profile updated.');
    }
}
