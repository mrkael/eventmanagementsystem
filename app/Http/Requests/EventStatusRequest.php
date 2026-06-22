<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission($this->route('eventStatus') ? 'event_statuses.update' : 'event_statuses.create') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('eventStatus')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('event_statuses', 'name')->ignore($id)],
            'key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/', Rule::unique('event_statuses', 'key')->ignore($id)],
            'color' => ['required', 'string', 'max:30'],
            'is_terminal' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
