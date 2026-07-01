<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'event_id', 'event_session_id', 'participant_registration_id', 'attendance_qr_token_id', 'scanned_by',
    'action', 'result', 'scan_token_hash', 'device_name', 'reason', 'notes', 'ip_address', 'user_agent',
    'latitude', 'longitude', 'location_name',
])]
class AttendanceLog extends Model
{
    use HasFactory;

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(EventSession::class, 'event_session_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(ParticipantRegistration::class, 'participant_registration_id');
    }

    public function qrToken(): BelongsTo
    {
        return $this->belongsTo(AttendanceQrToken::class, 'attendance_qr_token_id');
    }

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
