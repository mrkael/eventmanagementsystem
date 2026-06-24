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
            'session_id' => ['required_without:token', 'integer', 'exists:event_sessions,id'],
            'qr_token' => ['required_without:token', 'string', 'max:255'],
            'token' => ['required_without:qr_token', 'string', 'max:255'],
            'action' => ['nullable', 'in:check_in,check_out'],
            'device_name' => ['nullable', 'string', 'max:160'],
        ];
    }
}
