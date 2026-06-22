<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\PromoCodeRequest;
use App\Http\Requests\Core\TicketRequest;
use App\Models\Event;
use App\Models\PromoCode;
use App\Models\Ticket;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.core.tickets.index', [
            'event' => $event,
            'forms' => $event->registrationForms()->where('is_enabled', true)->orderBy('title')->get(),
            'tickets' => $event->tickets()->with('form')->latest()->paginate(10),
            'promoCodes' => $event->promoCodes()->with('ticket')->latest()->paginate(10, ['*'], 'promos'),
        ]);
    }

    public function store(TicketRequest $request, Event $event, AuditLogger $auditLogger): RedirectResponse
    {
        $ticket = $event->tickets()->create($request->validated() + [
            'available_quantity' => $request->integer('quantity'),
            'is_hidden' => $request->boolean('is_hidden'),
        ]);
        $auditLogger->record('core.tickets.create', "Created ticket {$ticket->name}.", $ticket);

        return back()->with('status', 'Ticket saved.');
    }

    public function update(TicketRequest $request, Event $event, Ticket $ticket, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($ticket->event_id === $event->id, 404);
        $sold = max(0, $ticket->quantity - $ticket->available_quantity);
        $payload = $request->validated();
        $payload['is_hidden'] = $request->boolean('is_hidden');
        $payload['available_quantity'] = max(0, (int) $payload['quantity'] - $sold);
        $ticket->update($payload);
        $auditLogger->record('core.tickets.update', "Updated ticket {$ticket->name}.", $ticket);

        return back()->with('status', 'Ticket updated.');
    }

    public function storePromo(PromoCodeRequest $request, Event $event, AuditLogger $auditLogger): RedirectResponse
    {
        $promo = $event->promoCodes()->create($request->validated() + ['used_count' => 0]);
        $auditLogger->record('core.promos.create', "Created promo code {$promo->code}.", $promo);

        return back()->with('status', 'Promo code saved.');
    }

    public function updatePromo(PromoCodeRequest $request, Event $event, PromoCode $promoCode, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($promoCode->event_id === $event->id, 404);
        $promoCode->update($request->validated());
        $auditLogger->record('core.promos.update', "Updated promo code {$promoCode->code}.", $promoCode);

        return back()->with('status', 'Promo code updated.');
    }
}
