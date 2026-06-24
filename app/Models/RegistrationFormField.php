<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['registration_form_id', 'source_type', 'field_key', 'type', 'label', 'key', 'placeholder', 'error_text', 'is_required', 'options', 'validation_rules', 'sort_order'])]
class RegistrationFormField extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = ['text', 'textarea', 'email', 'number', 'dropdown', 'radio', 'checkbox', 'date', 'file'];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'options' => 'array',
            'validation_rules' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(RegistrationForm::class, 'registration_form_id');
    }
}
