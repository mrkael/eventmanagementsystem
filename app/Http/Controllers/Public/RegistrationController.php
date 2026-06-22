<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registrations\ParticipantRegistrationRequest;
use App\Models\Event;
use App\Models\RegistrationInvite;
use App\Services\Registrations\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function show(Event $event): View
    {
        $form = $event->registrationForm()->with('groups.questions')->firstOrFail();
        abort_unless($event->is_public && $event->is_registration_enabled && $form->access_mode === 'public', 404);

        return view('public.registrations.show', compact('event', 'form'));
    }

    public function store(ParticipantRegistrationRequest $request, Event $event, RegistrationService $service): RedirectResponse
    {
        $service->register($event, $request->validated(), 'public');

        return redirect()->route('public.registrations.show', $event->slug)->with('status', 'Your registration has been received.');
    }

    public function private(Event $event): View
    {
        $form = $event->registrationForm()->with('groups.questions')->firstOrFail();
        abort_unless(auth()->check() && $event->is_registration_enabled && $form->access_mode === 'private', 404);

        return view('public.registrations.show', compact('event', 'form'));
    }

    public function storePrivate(ParticipantRegistrationRequest $request, Event $event, RegistrationService $service): RedirectResponse
    {
        $service->register($event, $request->validated(), 'private', $request->user());

        return redirect()->route('public.registrations.private.show', $event->slug)->with('status', 'Your private registration has been received.');
    }

    public function invite(string $token): View
    {
        $invite = RegistrationInvite::with('event', 'form.groups.questions')->where('token', $token)->firstOrFail();

        return view('public.registrations.show', [
            'event' => $invite->event,
            'form' => $invite->form,
            'invite' => $invite,
        ]);
    }

    public function storeInvite(ParticipantRegistrationRequest $request, string $token, RegistrationService $service): RedirectResponse
    {
        $invite = RegistrationInvite::with('event')->where('token', $token)->firstOrFail();
        $service->register($invite->event, $request->validated(), 'invite', null, $invite);

        return redirect()->route('public.registrations.invite.show', $token)->with('status', 'Your invite registration has been received.');
    }
}
