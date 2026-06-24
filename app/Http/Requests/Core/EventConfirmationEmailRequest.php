<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;

class EventConfirmationEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('emails.create') || $this->user()?->hasPermission('emails.send') || $this->user()?->hasPermission('events.update');
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'header_content' => ['nullable', 'string'],
            'body_content' => ['required', 'string'],
            'footer_content' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
