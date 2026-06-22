<?php

namespace App\Http\Controllers\Admin\Registrations;

use App\Enums\ParticipantRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Registrations\BulkParticipantUploadRequest;
use App\Http\Requests\Registrations\InviteRegistrationRequest;
use App\Http\Requests\Registrations\ParticipantRegistrationRequest;
use App\Models\Event;
use App\Models\ParticipantRegistration;
use App\Services\AuditLogger;
use App\Services\Registrations\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParticipantRegistrationController extends Controller
{
    public function index(Request $request, Event $event): View
    {
        $this->authorize('viewAny', ParticipantRegistration::class);

        $registrations = $event->participantRegistrations()
            ->with('invite')
            ->when($request->filled('search'), fn ($query) => $query
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('source'), fn ($query) => $query->where('source', $request->source))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.registrations.index', [
            'event' => $event->load('registrationForm'),
            'registrations' => $registrations,
            'statuses' => ParticipantRegistrationStatus::cases(),
        ]);
    }

    public function create(Event $event): View
    {
        $this->authorize('create', ParticipantRegistration::class);

        return view('admin.registrations.create', [
            'event' => $event->load('registrationForm.groups.questions'),
            'form' => $event->registrationForm,
        ]);
    }

    public function store(ParticipantRegistrationRequest $request, Event $event, RegistrationService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('create', ParticipantRegistration::class);
        $registration = $service->register($event, $request->validated(), 'admin', $request->user());
        $auditLogger->record('registrations.create', "Registered {$registration->email} for {$event->title}.", $registration, [], $registration->toArray());

        return redirect()->route('admin.events.registrations.show', [$event, $registration])->with('status', 'Participant registered successfully.');
    }

    public function show(Event $event, ParticipantRegistration $registration): View
    {
        abort_unless($registration->event_id === $event->id, 404);
        $this->authorize('view', $registration);

        return view('admin.registrations.show', [
            'event' => $event,
            'registration' => $registration->load('answers.files', 'invite'),
            'statuses' => ParticipantRegistrationStatus::cases(),
        ]);
    }

    public function updateStatus(Request $request, Event $event, ParticipantRegistration $registration, RegistrationService $service, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($registration->event_id === $event->id, 404);
        $this->authorize('update', $registration);
        $status = ParticipantRegistrationStatus::from($request->validate(['status' => ['required', Rule::in(array_column(ParticipantRegistrationStatus::cases(), 'value'))]])['status']);
        $old = $registration->toArray();
        $registration = $service->changeStatus($registration, $status);
        $auditLogger->record('registrations.status', "Changed {$registration->email} to {$status->label()}.", $registration, $old, $registration->toArray());

        return back()->with('status', 'Registration status updated.');
    }

    public function approve(Event $event, ParticipantRegistration $registration, RegistrationService $service, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($registration->event_id === $event->id, 404);
        $this->authorize('approve', $registration);
        $registration = $service->changeStatus($registration, ParticipantRegistrationStatus::Confirmed);
        $auditLogger->record('registrations.approve', "Approved {$registration->email} for {$event->title}.", $registration);

        return back()->with('status', 'Registration approved.');
    }

    public function bulk(BulkParticipantUploadRequest $request, Event $event, RegistrationService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $count = $service->bulkUpload($event, $request->file('file'), $request->user());
        $auditLogger->record('registrations.bulk_upload', "Bulk uploaded {$count} participants for {$event->title}.", $event);

        return back()->with('status', "{$count} participants imported.");
    }

    public function invite(InviteRegistrationRequest $request, Event $event, RegistrationService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $invite = $service->createInvite($event, $request->user(), $request->validated());
        $auditLogger->record('registrations.invite', "Created invite for {$invite->email}.", $invite);

        return back()->with('status', 'Invite link created: '.route('public.registrations.invite.show', $invite->token));
    }
}
