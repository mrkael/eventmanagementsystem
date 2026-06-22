<?php

namespace App\Http\Requests\Registrations;

use Illuminate\Foundation\Http\FormRequest;

class InviteRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('registrations.invite') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ];
    }
}
