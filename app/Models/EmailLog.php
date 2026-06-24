<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'event_id', 'registration_id', 'email_type', 'recipient_email', 'original_participant_email', 'sender_email', 'subject', 'status', 'error_message', 'sent_at',
])]
class EmailLog extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
