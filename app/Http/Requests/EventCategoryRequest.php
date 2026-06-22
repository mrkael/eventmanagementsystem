<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission($this->route('eventCategory') ? 'event_categories.update' : 'event_categories.create') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('eventCategory')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('event_categories', 'name')->ignore($id)],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('event_categories', 'slug')->ignore($id)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
