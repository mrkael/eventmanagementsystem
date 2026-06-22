<?php

namespace App\Policies;

use App\Models\ParticipantRegistration;
use App\Models\User;

class ParticipantRegistrationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('registrations.view');
    }

    public function view(User $user, ParticipantRegistration $registration): bool
    {
        return $user->hasPermission('registrations.view')
            || $registration->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('registrations.create');
    }

    public function update(User $user, ParticipantRegistration $registration): bool
    {
        return $user->hasPermission('registrations.update');
    }

    public function approve(User $user, ParticipantRegistration $registration): bool
    {
        return $user->hasPermission('registrations.approve');
    }

    public function delete(User $user, ParticipantRegistration $registration): bool
    {
        return $user->hasPermission('registrations.delete');
    }
}
