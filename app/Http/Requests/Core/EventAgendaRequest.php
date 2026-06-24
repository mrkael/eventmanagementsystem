<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;

class EventAgendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('events.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
        ];
    }
}
