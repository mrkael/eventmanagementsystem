<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['event_id', 'event_agenda_id', 'venue_id', 'created_by', 'updated_by', 'title', 'description', 'session_type', 'location', 'venue_name', 'starts_at', 'ends_at', 'capacity', 'sort_order', 'status', 'one_time_check_in', 'checkout_enabled'])]
class EventSession extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'capacity' => 'integer',
            'sort_order' => 'integer',
            'one_time_check_in' => 'boolean',
            'checkout_enabled' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(EventAgenda::class, 'event_agenda_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'session_tickets');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}
