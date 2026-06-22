<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['participant_registration_id', 'registration_question_id', 'question_key', 'question_label', 'question_type', 'value'])]
class ParticipantRegistrationAnswer extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(ParticipantRegistration::class, 'participant_registration_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(RegistrationQuestion::class, 'registration_question_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(RegistrationAnswerFile::class);
    }
}
