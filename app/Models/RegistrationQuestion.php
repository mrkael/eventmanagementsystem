<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'registration_form_id', 'registration_question_group_id', 'type', 'label', 'key',
    'help_text', 'is_required', 'options', 'validation_rules', 'conditional_logic', 'sort_order',
])]
class RegistrationQuestion extends Model
{
    use HasFactory;

    public const TYPES = ['text', 'textarea', 'email', 'number', 'date', 'dropdown', 'radio', 'checkbox', 'file'];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'options' => 'array',
            'validation_rules' => 'array',
            'conditional_logic' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(RegistrationForm::class, 'registration_form_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(RegistrationQuestionGroup::class, 'registration_question_group_id');
    }
}
