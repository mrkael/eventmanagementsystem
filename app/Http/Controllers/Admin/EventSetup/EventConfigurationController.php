<?php

namespace App\Http\Controllers\Admin\EventSetup;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventConfigurationRequest;
use App\Models\EventConfiguration;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EventConfigurationController extends Controller
{
    public function index(): View
    {
        return view('admin.event-setup.configuration.index', [
            'configurations' => EventConfiguration::latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.event-setup.configuration.create');
    }

    public function store(EventConfigurationRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $configuration = DB::transaction(function () use ($request) {
            if ($request->boolean('is_default')) {
                EventConfiguration::query()->update(['is_default' => false]);
            }

            return EventConfiguration::create($this->payload($request));
        });

        $auditLogger->record('event_configurations.create', "Created event configuration {$configuration->name}.", $configuration, [], $configuration->toArray());

        return redirect()->route('admin.event-configurations.index')->with('status', 'Event configuration created successfully.');
    }

    public function edit(EventConfiguration $eventConfiguration): View
    {
        return view('admin.event-setup.configuration.edit', ['configuration' => $eventConfiguration]);
    }

    public function update(EventConfigurationRequest $request, EventConfiguration $eventConfiguration, AuditLogger $auditLogger): RedirectResponse
    {
        $oldValues = $eventConfiguration->toArray();

        DB::transaction(function () use ($request, $eventConfiguration) {
            if ($request->boolean('is_default')) {
                EventConfiguration::whereKeyNot($eventConfiguration->id)->update(['is_default' => false]);
            }

            $eventConfiguration->update($this->payload($request));
        });

        $auditLogger->record('event_configurations.update', "Updated event configuration {$eventConfiguration->name}.", $eventConfiguration, $oldValues, $eventConfiguration->fresh()->toArray());

        return redirect()->route('admin.event-configurations.index')->with('status', 'Event configuration updated successfully.');
    }

    public function destroy(EventConfiguration $eventConfiguration, AuditLogger $auditLogger): RedirectResponse
    {
        if ($eventConfiguration->is_default) {
            return back()->withErrors(['configuration' => 'The default configuration cannot be deleted.']);
        }

        $oldValues = $eventConfiguration->toArray();
        $eventConfiguration->delete();
        $auditLogger->record('event_configurations.delete', "Deleted event configuration {$eventConfiguration->name}.", $eventConfiguration, $oldValues);

        return redirect()->route('admin.event-configurations.index')->with('status', 'Event configuration deleted successfully.');
    }

    private function payload(EventConfigurationRequest $request): array
    {
        $data = $request->validated();

        return [
            'name' => $data['name'],
            'is_default' => $request->boolean('is_default'),
            'registration_rules' => [
                'requires_approval' => $request->boolean('registration_requires_approval'),
                'allow_waitlist' => $request->boolean('registration_allow_waitlist'),
                'private_by_default' => $request->boolean('registration_private_by_default'),
                'open_days_before' => (int) $data['registration_open_days_before'],
            ],
            'qr_rules' => [
                'enabled' => $request->boolean('qr_enabled'),
                'expires_after_event_hours' => (int) $data['qr_expires_after_event_hours'],
                'allow_reuse' => $request->boolean('qr_allow_reuse'),
            ],
            'capacity_rules' => [
                'enforce_limit' => $request->boolean('capacity_enforce_limit'),
                'waitlist_when_full' => $request->boolean('capacity_waitlist_when_full'),
                'overbooking_limit' => (int) $data['capacity_overbooking_limit'],
            ],
            'email_settings' => [
                'send_confirmation' => $request->boolean('email_send_confirmation'),
                'send_reminder' => $request->boolean('email_send_reminder'),
                'reminder_hours_before' => (int) $data['email_reminder_hours_before'],
            ],
        ];
    }
}
