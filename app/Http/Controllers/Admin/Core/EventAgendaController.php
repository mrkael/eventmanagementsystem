<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\EventAgendaRequest;
use App\Http\Requests\Core\EventAgendaSessionRequest;
use App\Models\Event;
use App\Models\EventAgenda;
use App\Models\EventSession;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EventAgendaController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.core.agendas.index', [
            'event' => $event,
            'agendas' => $event->agendas()->withCount('sessions')->paginate(12),
        ]);
    }

    public function create(Event $event): View
    {
        return view('admin.core.agendas.create', [
            'event' => $event,
            'agenda' => null,
        ]);
    }

    public function store(EventAgendaRequest $request, Event $event, AuditLogger $auditLogger): RedirectResponse
    {
        $agenda = $event->agendas()->create([
            'title' => $request->validated('title'),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $auditLogger->record('core.agenda.create', "Created agenda {$agenda->title}.", $agenda);

        return redirect()->route('core.events.agendas.show', [$event, $agenda])->with('status', 'Agenda created. Add sessions next.');
    }

    public function edit(Event $event, EventAgenda $agenda): View
    {
        $this->assertAgendaBelongsToEvent($event, $agenda);

        return view('admin.core.agendas.create', [
            'event' => $event,
            'agenda' => $agenda,
        ]);
    }

    public function update(EventAgendaRequest $request, Event $event, EventAgenda $agenda, AuditLogger $auditLogger): RedirectResponse
    {
        $this->assertAgendaBelongsToEvent($event, $agenda);

        $agenda->update([
            'title' => $request->validated('title'),
            'updated_by' => $request->user()->id,
        ]);

        $auditLogger->record('core.agenda.update', "Updated agenda {$agenda->title}.", $agenda);

        return redirect()->route('core.events.agendas.show', [$event, $agenda])->with('status', 'Agenda updated.');
    }

    public function show(Event $event, EventAgenda $agenda): View
    {
        $this->assertAgendaBelongsToEvent($event, $agenda);

        return view('admin.core.agendas.show', [
            'event' => $event,
            'agenda' => $agenda->load(['sessions.tickets']),
            'tickets' => $event->tickets()->orderBy('name')->get(),
            'session' => null,
        ]);
    }

    public function storeSession(EventAgendaSessionRequest $request, Event $event, EventAgenda $agenda, AuditLogger $auditLogger): RedirectResponse
    {
        $this->assertAgendaBelongsToEvent($event, $agenda);

        $session = DB::transaction(function () use ($agenda, $event, $request) {
            $session = $agenda->sessions()->create([
                ...$request->sessionData(),
                'event_id' => $event->id,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);
            $session->tickets()->sync($request->validated('ticket_ids'));

            return $session;
        });

        $auditLogger->record('core.agenda.sessions.create', "Created session {$session->title}.", $session);

        return redirect()->route('core.events.agendas.show', [$event, $agenda])->with('status', 'Session saved.');
    }

    public function editSession(Event $event, EventAgenda $agenda, EventSession $session): View
    {
        $this->assertSessionBelongsToAgenda($event, $agenda, $session);

        return view('admin.core.agendas.show', [
            'event' => $event,
            'agenda' => $agenda->load(['sessions.tickets']),
            'tickets' => $event->tickets()->orderBy('name')->get(),
            'session' => $session->load('tickets'),
        ]);
    }

    public function updateSession(EventAgendaSessionRequest $request, Event $event, EventAgenda $agenda, EventSession $session, AuditLogger $auditLogger): RedirectResponse
    {
        $this->assertSessionBelongsToAgenda($event, $agenda, $session);

        DB::transaction(function () use ($request, $session) {
            $session->update([
                ...$request->sessionData(),
                'updated_by' => $request->user()->id,
            ]);
            $session->tickets()->sync($request->validated('ticket_ids'));
        });

        $auditLogger->record('core.agenda.sessions.update', "Updated session {$session->title}.", $session);

        return redirect()->route('core.events.agendas.show', [$event, $agenda])->with('status', 'Session updated.');
    }

    public function destroySession(Event $event, EventAgenda $agenda, EventSession $session, AuditLogger $auditLogger): RedirectResponse
    {
        $this->assertSessionBelongsToAgenda($event, $agenda, $session);

        $auditLogger->record('core.agenda.sessions.delete', "Deleted session {$session->title}.", $session);
        $session->tickets()->detach();
        $session->delete();

        return redirect()->route('core.events.agendas.show', [$event, $agenda])->with('status', 'Session deleted.');
    }

    public function destroy(Event $event, EventAgenda $agenda, AuditLogger $auditLogger): RedirectResponse
    {
        $this->assertAgendaBelongsToEvent($event, $agenda);

        $auditLogger->record('core.agenda.delete', "Deleted agenda {$agenda->title}.", $agenda);
        DB::transaction(function () use ($agenda) {
            $agenda->sessions()->with('tickets')->get()->each(function (EventSession $session) {
                $session->tickets()->detach();
                $session->delete();
            });
            $agenda->delete();
        });

        return redirect()->route('core.events.agendas.index', $event)->with('status', 'Agenda deleted.');
    }

    private function assertAgendaBelongsToEvent(Event $event, EventAgenda $agenda): void
    {
        abort_unless($agenda->event_id === $event->id, 404);
    }

    private function assertSessionBelongsToAgenda(Event $event, EventAgenda $agenda, EventSession $session): void
    {
        $this->assertAgendaBelongsToEvent($event, $agenda);
        abort_unless($session->event_id === $event->id && $session->event_agenda_id === $agenda->id, 404);
    }
}
