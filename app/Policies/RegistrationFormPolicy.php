<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\RegistrationForm;
use App\Models\User;

class RegistrationFormPolicy
{
    public function view(User $user, RegistrationForm $form): bool
    {
        return $user->hasPermission('registration_forms.view')
            || $form->event->organizer_id === $user->id;
    }

    public function manage(User $user, Event|RegistrationForm $target): bool
    {
        $event = $target instanceof RegistrationForm ? $target->event : $target;

        return $user->hasPermission('registration_forms.manage')
            || ($user->hasPermission('events.update') && $event->organizer_id === $user->id);
    }
}
