<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\ManualAttendeeRegistrationRequest;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Ticket;
use App\Services\AuditLogger;
use App\Services\Core\CoreRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendeeController extends Controller
{
    public function index(Request $request, Event $event): View
    {
        return view('admin.core.attendees.index', [
            'event' => $event,
            'tickets' => $event->tickets()->orderBy('name')->get(),
            'registrations' => $this->filteredRegistrations($request, $event)
                ->with('ticket', 'answers')
                ->latest()
                ->paginate(in_array((int) $request->input('per_page'), [10, 50, 100]) ? (int) $request->input('per_page') : 10)
                ->withQueryString(),
            'statuses' => ['confirmed', 'pending', 'cancelled', 'attended', 'no_show'],
        ]);
    }

    public function create(Event $event): View
    {
        return view('admin.core.attendees.create', [
            'event' => $event,
            'tickets' => $event->tickets()->with('form.fields')->orderBy('name')->get(),
        ]);
    }

    public function register(Event $event, Ticket $ticket): View
    {
        $this->assertTicketCanRegister($event, $ticket);

        return view('admin.core.attendees.register', [
            'event' => $event,
            'ticket' => $ticket->load('form.fields'),
            'registration' => null,
            'answers' => collect(),
        ]);
    }

    public function store(ManualAttendeeRegistrationRequest $request, Event $event, Ticket $ticket, CoreRegistrationService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $this->assertTicketCanRegister($event, $ticket);

        $registration = $service->registerManually($event, $ticket, $request->payload(), $request->user()->id);
        $auditLogger->record('core.attendees.manual', "Manually registered {$registration->reference_number}.", $registration);

        return redirect()->route('core.events.attendees.show', [$event, $registration])->with('status', 'Attendee registered and confirmation email sent.');
    }

    public function show(Event $event, Registration $registration): View
    {
        $this->assertRegistrationBelongsToEvent($event, $registration);

        return view('admin.core.attendees.show', [
            'event' => $event,
            'registration' => $registration->load('ticket', 'form.fields', 'answers', 'registeredBy'),
        ]);
    }

    public function edit(Event $event, Registration $registration): View
    {
        $this->assertRegistrationBelongsToEvent($event, $registration);

        $registration->load('ticket.form.fields', 'answers');

        return view('admin.core.attendees.register', [
            'event' => $event,
            'ticket' => $registration->ticket,
            'registration' => $registration,
            'answers' => $registration->answers->keyBy('field_key'),
        ]);
    }

    public function update(ManualAttendeeRegistrationRequest $request, Event $event, Registration $registration, CoreRegistrationService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $this->assertRegistrationBelongsToEvent($event, $registration);

        $registration = $service->updateManual($registration, $request->payload());
        $auditLogger->record('core.attendees.update', "Updated attendee {$registration->reference_number}.", $registration);

        return redirect()->route('core.events.attendees.show', [$event, $registration])->with('status', 'Attendee updated.');
    }

    public function resend(Event $event, Registration $registration, CoreRegistrationService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $this->assertRegistrationBelongsToEvent($event, $registration);

        $service->resendConfirmation($registration);
        $auditLogger->record('core.attendees.resend', "Resent confirmation for {$registration->reference_number}.", $registration);

        return back()->with('status', 'Confirmation email resent.');
    }

    public function cancel(Event $event, Registration $registration, AuditLogger $auditLogger): RedirectResponse
    {
        $this->assertRegistrationBelongsToEvent($event, $registration);

        if ($registration->status !== 'cancelled') {
            $registration->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            $registration->ticket()->increment('available_quantity');
        }

        $auditLogger->record('core.attendees.cancel', "Cancelled {$registration->reference_number}.", $registration);

        return back()->with('status', 'Registration cancelled.');
    }

    public function export(Request $request, Event $event)
    {
        $registrations = $this->filteredRegistrations($request, $event)
            ->with('ticket', 'answers')
            ->latest()
            ->get();

        $dynamicColumns = $this->dynamicColumns($registrations);
        $headers = collect([
            'Registration Reference',
            'Participant Name',
            'Participant Email',
            'Ticket Name',
            'Registration Date',
            'Registration Status',
            'Confirmation Email Sent At',
            'QR Status',
            'Referral',
        ])->merge($dynamicColumns);

        $rows = $registrations->map(function (Registration $registration) use ($dynamicColumns) {
            $answers = $registration->answers->keyBy('field_label');

            return collect([
                $registration->reference_number,
                $registration->full_name,
                $registration->email,
                $registration->ticket?->name,
                $registration->created_at?->format('Y-m-d H:i:s'),
                $registration->status,
                $registration->confirmation_email_sent_at?->format('Y-m-d H:i:s'),
                $registration->qr_token ? 'Generated' : 'Missing',
                $registration->referral ?? '',
            ])->merge($dynamicColumns->map(fn (string $column) => $this->answerValue($answers->get($column))))->values();
        });

        return Response::make($this->spreadsheetXml($headers, $rows), 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="attendees-'.$event->id.'.xls"',
        ]);
    }

    private function filteredRegistrations(Request $request, Event $event)
    {
        return $event->coreRegistrations()
            ->when($request->filled('ticket_id'), fn ($query) => $query->where('ticket_id', $request->ticket_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('registered_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->registered_from))
            ->when($request->filled('registered_until'), fn ($query) => $query->whereDate('created_at', '<=', $request->registered_until))
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request) {
                $query->where('full_name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('reference_number', 'like', "%{$request->search}%");
            }));
    }

    private function assertTicketCanRegister(Event $event, Ticket $ticket): void
    {
        abort_unless($ticket->event_id === $event->id, 404);
        $ticket->loadMissing('form.fields');

        if ($ticket->status !== 'active') {
            throw ValidationException::withMessages(['ticket' => 'Selected ticket is not active.']);
        }

        if ($ticket->available_quantity < 1) {
            throw ValidationException::withMessages(['ticket' => 'Selected ticket has no available quantity.']);
        }

        if (! $ticket->form) {
            throw ValidationException::withMessages(['ticket' => 'Selected ticket does not have an assigned registration form.']);
        }
    }

    private function assertRegistrationBelongsToEvent(Event $event, Registration $registration): void
    {
        abort_unless($registration->event_id === $event->id, 404);
    }

    private function dynamicColumns(Collection $registrations): Collection
    {
        return $registrations
            ->flatMap(fn (Registration $registration) => $registration->answers->pluck('field_label'))
            ->filter()
            ->unique()
            ->values();
    }

    private function answerValue($answer): string
    {
        if (! $answer) {
            return '';
        }

        if ($answer->file_path) {
            return $answer->file_path;
        }

        return is_array($answer->value) ? implode(', ', $answer->value) : (string) $answer->value;
    }

    private function spreadsheetXml(Collection $headers, Collection $rows): string
    {
        $sheetRows = collect([$headers])->merge($rows)->map(function (Collection $row) {
            $cells = $row->map(fn ($value) => '<Cell><Data ss:Type="String">'.e((string) $value).'</Data></Cell>')->implode('');

            return '<Row>'.$cells.'</Row>';
        })->implode('');

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<?mso-application progid="Excel.Sheet"?>'
            .'<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            .'<Worksheet ss:Name="Attendees"><Table>'.$sheetRows.'</Table></Worksheet></Workbook>';
    }
}
