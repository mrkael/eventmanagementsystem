<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Services\AuditLogger;
use App\Services\Core\CoreRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendeeController extends Controller
{
    public function index(Request $request): View
    {
        $events = Event::orderByDesc('starts_at')->get();
        $query = Registration::with('event', 'ticket', 'answers')
            ->when($request->filled('event_id'), fn ($query) => $query->where('event_id', $request->event_id))
            ->when($request->filled('ticket_id'), fn ($query) => $query->where('ticket_id', $request->ticket_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request) {
                $query->where('full_name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('reference_number', 'like', "%{$request->search}%");
            }))
            ->latest();

        return view('admin.core.attendees.index', [
            'events' => $events,
            'tickets' => $request->filled('event_id') ? Event::find($request->event_id)?->tickets()->orderBy('name')->get() ?? collect() : collect(),
            'registrations' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function store(Request $request, CoreRegistrationService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'ticket_id' => ['required', 'exists:tickets,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'organization' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
        ]);
        $event = Event::findOrFail($data['event_id']);
        $ticket = $event->tickets()->findOrFail($data['ticket_id']);
        $registration = $service->register($event, $ticket, $data);
        $auditLogger->record('core.attendees.manual', "Manually registered {$registration->reference_number}.", $registration);

        return back()->with('status', 'Participant registered and e-ticket sent.');
    }

    public function import(Request $request, CoreRegistrationService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'ticket_id' => ['required', 'exists:tickets,id'],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);
        $event = Event::findOrFail($data['event_id']);
        $ticket = $event->tickets()->findOrFail($data['ticket_id']);
        $handle = fopen($data['file']->getRealPath(), 'r');
        $headers = array_map('trim', fgetcsv($handle) ?: []);
        $created = 0;
        $failed = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $payload = array_combine($headers, $row) ?: [];
            try {
                $service->register($event, $ticket, [
                    'full_name' => $payload['full_name'] ?? $payload['name'] ?? null,
                    'email' => $payload['email'] ?? null,
                    'phone' => $payload['phone'] ?? null,
                    'organization' => $payload['organization'] ?? null,
                    'designation' => $payload['designation'] ?? null,
                ]);
                $created++;
            } catch (ValidationException) {
                $failed++;
            }
        }
        fclose($handle);
        $auditLogger->record('core.attendees.import', "Imported {$created} participants, {$failed} failed.", $event);

        return back()->with('status', "Imported {$created} participants. {$failed} rows failed validation.");
    }

    public function resend(Registration $registration, CoreRegistrationService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $service->resendConfirmation($registration);
        $auditLogger->record('core.attendees.resend', "Resent confirmation for {$registration->reference_number}.", $registration);

        return back()->with('status', 'Confirmation email resent.');
    }

    public function cancel(Registration $registration, AuditLogger $auditLogger): RedirectResponse
    {
        $registration->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        $registration->ticket()->increment('available_quantity');
        $auditLogger->record('core.attendees.cancel', "Cancelled {$registration->reference_number}.", $registration);

        return back()->with('status', 'Registration cancelled.');
    }

    public function export(Request $request)
    {
        $rows = Registration::with('event', 'ticket')
            ->when($request->filled('event_id'), fn ($query) => $query->where('event_id', $request->event_id))
            ->when($request->filled('ticket_id'), fn ($query) => $query->where('ticket_id', $request->ticket_id))
            ->latest()
            ->get();
        $csv = "Reference,Event,Ticket,Name,Email,Phone,Status,Payment Status,Registered At\n";
        foreach ($rows as $row) {
            $csv .= collect([
                $row->reference_number,
                $row->event?->title,
                $row->ticket?->name,
                $row->full_name,
                $row->email,
                $row->phone,
                $row->status,
                $row->payment_status,
                $row->created_at,
            ])->map(fn ($value) => '"'.str_replace('"', '""', (string) $value).'"')->implode(',')."\n";
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendees.csv"',
        ]);
    }
}
