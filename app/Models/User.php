<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'department_id', 'phone', 'position', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $roleKey): bool
    {
        $this->loadMissing('roles');

        return $this->roles->contains('key', $roleKey);
    }

    public function hasAnyRole(array $roleKeys): bool
    {
        $this->loadMissing('roles');

        return $this->roles->pluck('key')->intersect($roleKeys)->isNotEmpty();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isPlatformAdmin(): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin']);
    }

    public function isOrganiser(): bool
    {
        return $this->hasRole('organizer');
    }

    public function organiserProfile(): HasOne
    {
        return $this->hasOne(OrganiserProfile::class);
    }

    public function ownsEvent(Event $event): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        $this->loadMissing('organiserProfile');

        return $this->organiserProfile
            && (int) $event->organiser_profile_id === (int) $this->organiserProfile->id;
    }

    public function hasPermission(string $permissionKey): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $this->loadMissing('roles.permissions');

        return $this->roles
            ->flatMap(fn (Role $role) => $role->permissions)
            ->contains('key', $permissionKey);
    }
}
