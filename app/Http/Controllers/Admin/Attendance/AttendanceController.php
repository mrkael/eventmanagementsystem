<?php

namespace App\Http\Controllers\Admin\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\AttendanceOverrideRequest;
use App\Http\Requests\Attendance\AttendanceScanRequest;
use App\Models\Event;
use App\Models\ParticipantRegistration;
use App\Services\Attendance\AttendanceService;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Event $event, AttendanceService $service): View
    {
        $this->authorize('attendance.view', $event);

        return view('admin.attendance.index', [
            'event' => $event,
            'metrics' => $service->dashboard($event),
            'registrations' => $event->participantRegistrations()->latest()->paginate(15),
            'logs' => $event->attendanceLogs()->with('registration', 'scanner')->latest()->limit(20)->get(),
        ]);
    }

    public function scanner(Event $event): View
    {
        $this->authorize('attendance.scan', $event);

        return view('admin.attendance.scanner', ['event' => $event->load('sessions')]);
    }

    public function checkIn(AttendanceScanRequest $request, Event $event, AttendanceService $service, AuditLogger $auditLogger): JsonResponse
    {
        try {
            $registration = $service->checkIn($event, $request->string('token'), $request->user(), $request->validated());
            $auditLogger->record('attendance.check_in', "Checked in {$registration->email} for {$event->title}.", $registration);
        } catch (ValidationException $exception) {
            return response()->json(['message' => collect($exception->errors())->flatten()->first(), 'errors' => $exception->errors()], 422);
        }

        return response()->json(['message' => 'Checked in successfully.', 'registration' => $this->payload($registration)]);
    }

    public function checkOut(AttendanceScanRequest $request, Event $event, AttendanceService $service, AuditLogger $auditLogger): JsonResponse
    {
        try {
            $registration = $service->checkOut($event, $request->string('token'), $request->user(), $request->validated());
            $auditLogger->record('attendance.check_out', "Checked out {$registration->email} for {$event->title}.", $registration);
        } catch (ValidationException $exception) {
            return response()->json(['message' => collect($exception->errors())->flatten()->first(), 'errors' => $exception->errors()], 422);
        }

        return response()->json(['message' => 'Checked out successfully.', 'registration' => $this->payload($registration)]);
    }

    public function generate(Event $event, ParticipantRegistration $registration, AttendanceService $service, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($registration->event_id === $event->id, 404);
        $this->authorize('attendance.scan', $event);

        $qr = $service->generateQr($registration, request()->user());
        $auditLogger->record('attendance.qr_generate', "Generated QR for {$registration->email}.", $registration);

        return back()->with('attendance_token', $qr['token'])->with('attendance_token_registration', $registration->id)->with('status', 'Attendance QR generated.');
    }

    public function override(AttendanceOverrideRequest $request, Event $event, ParticipantRegistration $registration, AttendanceService $service, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($registration->event_id === $event->id, 404);
        $updated = $service->manualOverride($event, $registration, $request->user(), $request->string('action'), $request->string('reason'), $request->input('notes'));
        $auditLogger->record('attendance.override', "Manual attendance override for {$updated->email}.", $updated, [], $request->validated());

        return back()->with('status', 'Attendance override recorded.');
    }

    public function export(Event $event, string $format, AttendanceService $service): Response
    {
        $this->authorize('attendance.export', $event);

        abort_unless(in_array($format, ['excel', 'pdf'], true), 404);

        return $format === 'excel' ? $service->exportExcel($event) : $service->exportPdf($event);
    }

    private function payload(ParticipantRegistration $registration): array
    {
        return [
            'name' => $registration->name,
            'email' => $registration->email,
            'status' => $registration->status->label(),
            'checked_in_at' => $registration->checked_in_at?->format('Y-m-d H:i'),
            'checked_out_at' => $registration->checked_out_at?->format('Y-m-d H:i'),
        ];
    }
}
