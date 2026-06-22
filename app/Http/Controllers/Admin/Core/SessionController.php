<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\SessionRequest;
use App\Models\Event;
use App\Models\EventSession;
use App\Services\AuditLogger;
use App\Services\Core\CoreAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.core.sessions.index', [
            'event' => $event,
            'tickets' => $event->tickets()->orderBy('name')->get(),
            'sessions' => $event->sessions()->with('tickets')->paginate(10),
        ]);
    }

    public function store(SessionRequest $request, Event $event, AuditLogger $auditLogger): RedirectResponse
    {
        $session = $event->sessions()->create($request->validated());
        $session->tickets()->sync($request->input('ticket_ids', []));
        $auditLogger->record('core.sessions.create', "Created session {$session->title}.", $session);

        return back()->with('status', 'Session saved.');
    }

    public function update(SessionRequest $request, Event $event, EventSession $session, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($session->event_id === $event->id, 404);
        $session->update($request->validated());
        $session->tickets()->sync($request->input('ticket_ids', []));
        $auditLogger->record('core.sessions.update', "Updated session {$session->title}.", $session);

        return back()->with('status', 'Session updated.');
    }

    public function scanner(Event $event, EventSession $session): View
    {
        abort_unless($session->event_id === $event->id, 404);

        return view('admin.core.sessions.scanner', compact('event', 'session'));
    }

    public function counter(Event $event, EventSession $session, CoreAttendanceService $service): JsonResponse
    {
        abort_unless($session->event_id === $event->id, 404);

        return response()->json($service->counter($session));
    }
}
