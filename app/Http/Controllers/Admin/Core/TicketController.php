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
            'tickets' => $event->tickets()->with('form')->latest()->paginate(10),
        ]);
    }

    public function create(Event $event): View
    {
        return view('admin.core.tickets.create', [
            'event' => $event,
            'ticket' => new Ticket(['status' => 'active', 'min_quantity' => 1, 'max_quantity' => 1]),
        ]);
    }

    public function store(TicketRequest $request, Event $event, AuditLogger $auditLogger): RedirectResponse
    {
        $ticket = $event->tickets()->create($this->payload($request, $event) + [
            'available_quantity' => $request->integer('quantity'),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);
        $auditLogger->record('tickets.create', "Created ticket {$ticket->name}.", $ticket);

        return redirect()->route('core.events.tickets.index', $event)->with('status', 'Ticket saved.');
    }

    public function edit(Event $event, Ticket $ticket): View
    {
        abort_unless($ticket->event_id === $event->id, 404);

        return view('admin.core.tickets.edit', [
            'event' => $event,
            'ticket' => $ticket,
        ]);
    }

    public function update(TicketRequest $request, Event $event, Ticket $ticket, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($ticket->event_id === $event->id, 404);
        $sold = max(0, $ticket->quantity - $ticket->available_quantity);
        $payload = $this->payload($request, $event);
        $payload['updated_by'] = $request->user()->id;
        $payload['available_quantity'] = max(0, (int) $payload['quantity'] - $sold);
        $ticket->update($payload);
        $auditLogger->record('tickets.update', "Updated ticket {$ticket->name}.", $ticket);

        return redirect()->route('core.events.tickets.index', $event)->with('status', 'Ticket updated.');
    }

    public function destroy(Event $event, Ticket $ticket, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($ticket->event_id === $event->id, 404);
        $auditLogger->record('tickets.delete', "Deleted ticket {$ticket->name}.", $ticket);
        $ticket->delete();

        return redirect()->route('core.events.tickets.index', $event)->with('status', 'Ticket deleted.');
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

    private function payload(TicketRequest $request, Event $event): array
    {
        $data = $request->validated();

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'currency' => 'MYR',
            'price' => 0,
            'quantity' => $data['quantity'],
            'min_quantity' => $data['min_quantity'],
            'max_quantity' => $data['max_quantity'],
            'sales_start_at' => $data['sales_start_at'],
            'sales_end_at' => $data['sales_end_at'],
            'is_hidden' => $request->boolean('is_hidden'),
            'status' => $data['status'],
        ];
    }
}
