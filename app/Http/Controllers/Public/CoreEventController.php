<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\PublicRegistrationRequest;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Ticket;
use App\Services\Core\CoreRegistrationService;
use App\Services\Core\MicrositeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class CoreEventController extends Controller
{
    public function show(Event $event, MicrositeService $microsite, ?string $referral = null): View
    {
        abort_unless($event->is_public, 404);

        return view('public.core.events.show', [
            'event' => $event->load('publishedPage.sections'),
            'tickets' => $microsite->activeVisibleTickets($event),
            'referral' => $this->sanitizeReferral($referral),
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
        $data = $request->validated();

        $referral = $this->sanitizeReferral($data['referral'] ?? null);

        if (! empty($data['participants'])) {
            $registrations = collect($data['participants'])
                ->filter(fn (array $participant) => filled($participant['full_name'] ?? null) && filled($participant['email'] ?? null))
                ->map(function (array $participant) use ($data, $event, $referral, $service, $ticket) {
                    $answers = [];
                    $answerFiles = [];

                    foreach ($participant as $key => $value) {
                        if ($value instanceof UploadedFile) {
                            $answerFiles[$key] = $value;
                        } else {
                            $answers[$key] = $value;
                        }
                    }

                    return $service->register($event, $ticket, [
                        'full_name' => $participant['full_name'],
                        'email' => $participant['email'],
                        'phone' => $participant['phone_number'] ?? $participant['phone'] ?? null,
                        'organization' => $participant['organization'] ?? null,
                        'designation' => $participant['designation'] ?? null,
                        'promo_code' => $data['promo_code'] ?? null,
                        'referral' => $referral,
                        'answers' => $answers,
                        'answer_files' => $answerFiles,
                    ]);
                });

            $registration = $registrations->first();
            abort_unless($registration, 422);
        } else {
            $registration = $service->register($event, $ticket, [...$data, 'referral' => $referral]);
        }

        return redirect()->to(URL::signedRoute('core.public.success', $registration))->with('status', 'Registration confirmed. Please check your email for the e-ticket.');
    }

    private function sanitizeReferral(?string $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        $value = substr($value, 0, 100);

        if ($value === '' || in_array(strtolower($value), ['n/a', 'na', 'null', 'nil', 'none'], true)) {
            return null;
        }

        if (! preg_match('/^[a-zA-Z0-9_\-\.]+$/', $value)) {
            return null;
        }

        return $value;
    }

    public function success(Registration $registration): View
    {
        return view('public.core.events.success', ['registration' => $registration->load('event', 'ticket')]);
    }
}
