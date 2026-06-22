<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['participant_registration_answer_id', 'original_name', 'path', 'mime_type', 'size'])]
class RegistrationAnswerFile extends Model
{
    use HasFactory;

    public function answer(): BelongsTo
    {
        return $this->belongsTo(ParticipantRegistrationAnswer::class, 'participant_registration_answer_id');
    }
}
