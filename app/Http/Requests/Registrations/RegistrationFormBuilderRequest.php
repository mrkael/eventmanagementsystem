<?php

namespace App\Http\Requests\Registrations;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationFormBuilderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event
            && ($this->user()?->hasPermission('registration_forms.manage')
                || ($this->user()?->hasPermission('events.update') && $event->organizer_id === $this->user()?->id));
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'access_mode' => ['required', Rule::in(['public', 'private', 'invite'])],
            'is_enabled' => ['nullable', 'boolean'],
            'requires_approval' => ['nullable', 'boolean'],
            'allow_waitlist' => ['nullable', 'boolean'],
            'is_multi_step' => ['nullable', 'boolean'],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after_or_equal:opens_at'],
            'schema' => ['required', 'json'],
        ];
    }
}
