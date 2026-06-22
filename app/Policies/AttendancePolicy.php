<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class AttendancePolicy
{
    public function view(User $user, Event $event): bool
    {
        return $user->hasPermission('attendance.view')
            || ($user->hasPermission('events.view') && $event->organizer_id === $user->id);
    }

    public function scan(User $user, Event $event): bool
    {
        return $user->hasPermission('attendance.scan')
            || ($user->hasPermission('events.update') && $event->organizer_id === $user->id);
    }

    public function override(User $user, Event $event): bool
    {
        return $user->hasPermission('attendance.override');
    }

    public function export(User $user, Event $event): bool
    {
        return $user->hasPermission('attendance.export');
    }
}
