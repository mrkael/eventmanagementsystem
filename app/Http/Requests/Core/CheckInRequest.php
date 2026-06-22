<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('attendance.scan') ?? false;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:160'],
            'action' => ['nullable', 'in:check_in,check_out'],
            'device_name' => ['nullable', 'string', 'max:160'],
        ];
    }
}
