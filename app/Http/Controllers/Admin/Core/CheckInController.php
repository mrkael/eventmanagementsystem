<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\CheckInRequest;
use App\Models\Event;
use App\Models\EventSession;
use App\Services\Core\CoreAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckInController extends Controller
{
    public function index(Request $request, Event $event, CoreAttendanceService $service): View
    {
        $sessions = $event->sessions()->with('tickets')->get();
        $selectedSession = $request->filled('session_id')
            ? $sessions->firstWhere('id', (int) $request->integer('session_id'))
            : $sessions->first();

        return view('admin.core.check-in.index', [
            'event' => $event,
            'sessions' => $sessions,
            'selectedSession' => $selectedSession,
            'counts' => $selectedSession ? $service->counter($selectedSession) : ['eligible' => 0, 'checked_in' => 0, 'pending' => 0, 'percentage' => 0],
            'records' => $selectedSession ? $service->checkedInRecords($selectedSession) : collect(),
        ]);
    }

    public function scan(CheckInRequest $request, Event $event, CoreAttendanceService $service): JsonResponse
    {
        $session = $event->sessions()->with('tickets')->findOrFail($request->integer('session_id'));

        if ($session->tickets->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No ticket is assigned to this session. Assign tickets in Agenda before check-in.',
                'counts' => $service->counter($session),
            ], 422);
        }

        try {
            $registration = $service->scan(
                event: $event,
                session: $session,
                rawToken: $request->string('qr_token')->toString(),
                action: 'check_in',
                user: $request->user(),
                meta: ['device_name' => $request->input('device_name')]
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first(),
                'counts' => $service->counter($session),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful.',
            'participant_name' => $registration->full_name,
            'participant_email' => $registration->email,
            'ticket_name' => $registration->ticket?->name,
            'session_name' => $session->title,
            'registration_reference' => $registration->reference_number,
            'checked_in_at' => now()->format('d M Y, H:i:s'),
            'counts' => $service->counter($session),
            'records' => $this->recordsPayload($service->checkedInRecords($session), $event),
        ]);
    }

    public function __invoke(CheckInRequest $request, Event $event, EventSession $session, CoreAttendanceService $service): JsonResponse
    {
        $request->merge(['session_id' => $session->id, 'qr_token' => $request->input('token')]);

        return $this->scan($request, $event, $service);
    }

    private function recordsPayload($records, Event $event): array
    {
        return $records->map(fn ($record) => [
            'participant_name' => $record->registration?->full_name,
            'participant_email' => $record->registration?->email,
            'ticket_name' => $record->registration?->ticket?->name,
            'registration_reference' => $record->registration?->reference_number,
            'checked_in_at' => $record->checked_in_at?->format('d M Y, H:i:s'),
            'checked_in_by' => $record->checkedInBy?->name,
            'attendee_url' => $record->registration ? route('core.events.attendees.show', [$event, $record->registration]) : null,
        ])->values()->all();
    }
}
