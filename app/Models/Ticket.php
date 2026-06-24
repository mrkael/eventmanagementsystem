<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'event_id', 'created_by', 'updated_by', 'registration_form_id', 'name', 'description', 'currency', 'min_quantity', 'max_quantity', 'is_hidden', 'price', 'early_bird_price',
    'group_min_quantity', 'group_price', 'quantity', 'available_quantity',
    'sales_start_at', 'sales_end_at', 'status',
])]
class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'early_bird_price' => 'decimal:2',
            'group_price' => 'decimal:2',
            'is_hidden' => 'boolean',
            'sales_start_at' => 'datetime',
            'sales_end_at' => 'datetime',
            'quantity' => 'integer',
            'available_quantity' => 'integer',
            'group_min_quantity' => 'integer',
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(RegistrationForm::class, 'registration_form_id');
    }

    public function promoCodes(): HasMany
    {
        return $this->hasMany(PromoCode::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(EventSession::class, 'session_tickets');
    }
}
