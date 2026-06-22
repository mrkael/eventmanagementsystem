<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['registration_form_id', 'title', 'description', 'step_number', 'sort_order'])]
class RegistrationQuestionGroup extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'step_number' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(RegistrationForm::class, 'registration_form_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(RegistrationQuestion::class)->orderBy('sort_order');
    }
}
