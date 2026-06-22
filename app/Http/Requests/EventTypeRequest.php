<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission($this->route('eventType') ? 'event_types.update' : 'event_types.create') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('eventType')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('event_types', 'name')->ignore($id)],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('event_types', 'slug')->ignore($id)],
            'description' => ['nullable', 'string', 'max:500'],
            'requires_approval' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
