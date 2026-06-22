<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['event_id', 'email_template_id', 'created_by', 'type', 'sender_name', 'sender_email', 'subject', 'preheader', 'recipient_filters', 'recipient_count', 'status', 'scheduled_at', 'sent_at'])]
class EmailCampaign extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'recipient_filters' => 'array',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }
}
