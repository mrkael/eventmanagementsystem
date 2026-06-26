<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class AttendancePolicy
{
    public function view(User $user, Event $event): bool
    {
        return $user->hasPermission('attendance.view') && $user->ownsEvent($event);
    }

    public function scan(User $user, Event $event): bool
    {
        return $user->hasPermission('attendance.scan') && $user->ownsEvent($event);
    }

    public function override(User $user, Event $event): bool
    {
        return $user->hasPermission('attendance.override') && $user->ownsEvent($event);
    }

    public function export(User $user, Event $event): bool
    {
        return $user->hasPermission('attendance.export') && $user->ownsEvent($event);
    }
}
