<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\PublicRegistrationRequest;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Ticket;
use App\Services\Core\CoreRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class CoreEventController extends Controller
{
    public function show(Event $event): View
    {
        abort_unless($event->is_public, 404);

        return view('public.core.events.show', [
            'event' => $event->load('publishedPage.sections'),
            'tickets' => $event->tickets()->where('status', 'active')->where('is_hidden', false)->where('available_quantity', '>', 0)->orderBy('price')->get(),
        ]);
    }

    public function register(Event $event, Ticket $ticket): View
    {
        abort_unless($ticket->event_id === $event->id && $event->is_public, 404);

        return view('public.core.events.register', [
            'event' => $event,
            'ticket' => $ticket->load('form.fields'),
        ]);
    }

    public function submit(PublicRegistrationRequest $request, Event $event, Ticket $ticket, CoreRegistrationService $service): RedirectResponse
    {
        abort_unless($ticket->event_id === $event->id && $event->is_public, 404);
        $registration = $service->register($event, $ticket, $request->validated());

        return redirect()->to(URL::signedRoute('core.public.success', $registration))->with('status', 'Registration confirmed. Please check your email for the e-ticket.');
    }

    public function success(Registration $registration): View
    {
        return view('public.core.events.success', ['registration' => $registration->load('event', 'ticket')]);
    }
}
