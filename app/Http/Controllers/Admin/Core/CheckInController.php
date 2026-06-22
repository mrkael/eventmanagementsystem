<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\CheckInRequest;
use App\Models\Event;
use App\Models\EventSession;
use App\Services\Core\CoreAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CheckInController extends Controller
{
    public function __invoke(CheckInRequest $request, Event $event, EventSession $session, CoreAttendanceService $service): JsonResponse
    {
        abort_unless($session->event_id === $event->id, 404);

        try {
            $registration = $service->scan(
                event: $event,
                session: $session,
                rawToken: $request->string('token'),
                action: $request->input('action', 'check_in'),
                user: $request->user(),
                meta: ['device_name' => $request->input('device_name')]
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'ok' => false,
                'message' => collect($exception->errors())->flatten()->first(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => $request->input('action') === 'check_out' ? 'Check-out successful.' : 'Check-in successful.',
            'participant' => [
                'name' => $registration->full_name,
                'email' => $registration->email,
                'ticket' => $registration->ticket?->name,
                'reference' => $registration->reference_number,
            ],
        ]);
    }
}
