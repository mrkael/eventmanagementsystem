<?php

namespace App\Services\Core;

use App\Enums\UserStatus;
use App\Mail\OrganiserLoginAccessMail;
use App\Models\OrganiserProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class OrganiserLoginAccessService
{
    public function ensureAccess(OrganiserProfile $organiser, bool $issueNewPassword = false): array
    {
        $user = $organiser->user ?: User::where('email', $organiser->email)->first();
        $temporaryPassword = null;

        if (! $user) {
            $temporaryPassword = $this->temporaryPassword();
            $user = User::create([
                'name' => $organiser->name,
                'email' => $organiser->email,
                'password' => Hash::make($temporaryPassword),
                'status' => UserStatus::Active->value,
            ]);
        } elseif ($issueNewPassword) {
            $temporaryPassword = $this->temporaryPassword();
            $user->forceFill([
                'name' => $user->name ?: $organiser->name,
                'email' => $organiser->email,
                'password' => Hash::make($temporaryPassword),
                'status' => UserStatus::Active->value,
            ])->save();
        } else {
            $user->forceFill([
                'name' => $user->name ?: $organiser->name,
                'email' => $organiser->email,
                'status' => UserStatus::Active->value,
            ])->save();
        }

        $role = Role::firstOrCreate(
            ['key' => 'organizer'],
            ['name' => 'Organiser', 'description' => 'Organiser access profile.', 'is_system' => true],
        );

        $user->roles()->syncWithoutDetaching([$role->id]);

        if ($organiser->user_id !== $user->id) {
            $organiser->forceFill(['user_id' => $user->id])->save();
        }

        return $this->sendAccessEmail($organiser->fresh('user'), $temporaryPassword);
    }

    public function resendAccess(OrganiserProfile $organiser): array
    {
        return $this->ensureAccess($organiser, true);
    }

    private function sendAccessEmail(OrganiserProfile $organiser, ?string $temporaryPassword): array
    {
        try {
            Mail::to($organiser->email)->send(new OrganiserLoginAccessMail($organiser, $organiser->user, $temporaryPassword));

            return ['sent' => true, 'message' => 'Login access email sent.'];
        } catch (Throwable $exception) {
            report($exception);

            return ['sent' => false, 'message' => 'Organiser profile saved, but the login access email could not be sent.'];
        }
    }

    private function temporaryPassword(): string
    {
        return Str::password(14);
    }
}
