<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('events.view');
    }

    public function view(User $user, Event $event): bool
    {
        return $user->hasPermission('events.view') || $event->organizer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('events.create');
    }

    public function update(User $user, Event $event): bool
    {
        return $user->hasPermission('events.update') || ($user->hasPermission('events.create') && $event->organizer_id === $user->id);
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->hasPermission('events.delete');
    }

    public function submit(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }

    public function publish(User $user, Event $event): bool
    {
        return $user->hasPermission('events.publish');
    }
}
