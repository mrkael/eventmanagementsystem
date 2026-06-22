<?php

namespace App\Http\Requests\Registrations;

use App\Models\ParticipantRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class BulkParticipantUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', ParticipantRegistration::class);
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:5120', 'mimes:csv,txt'],
        ];
    }
}
