<?php

namespace App\Models;

use App\Enums\ParticipantRegistrationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'event_id', 'registration_form_id', 'user_id', 'registration_invite_id', 'name', 'email',
    'phone', 'organization', 'status', 'source', 'approved_at', 'cancelled_at',
    'checked_in_at', 'checked_out_at', 'attendance_notes',
])]
class ParticipantRegistration extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => ParticipantRegistrationStatus::class,
            'approved_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(RegistrationForm::class, 'registration_form_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invite(): BelongsTo
    {
        return $this->belongsTo(RegistrationInvite::class, 'registration_invite_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ParticipantRegistrationAnswer::class);
    }

    public function attendanceQrTokens(): HasMany
    {
        return $this->hasMany(AttendanceQrToken::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }
}
