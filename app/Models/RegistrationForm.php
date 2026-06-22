<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'event_id', 'title', 'description', 'access_mode', 'is_enabled', 'requires_approval',
    'allow_waitlist', 'is_multi_step', 'opens_at', 'closes_at', 'settings',
])]
class RegistrationForm extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'requires_approval' => 'boolean',
            'allow_waitlist' => 'boolean',
            'is_multi_step' => 'boolean',
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(RegistrationQuestionGroup::class)->orderBy('sort_order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(RegistrationQuestion::class)->orderBy('sort_order');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(RegistrationFormField::class)->orderBy('sort_order');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(ParticipantRegistration::class);
    }

    public function invites(): HasMany
    {
        return $this->hasMany(RegistrationInvite::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
