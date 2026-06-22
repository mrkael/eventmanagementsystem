<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['event_id', 'created_by', 'name', 'module', 'selected_columns', 'show_on_overview'])]
class EventReport extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'selected_columns' => 'array',
            'show_on_overview' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
