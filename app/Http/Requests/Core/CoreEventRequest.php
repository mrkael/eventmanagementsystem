<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        if ($event && ! $this->user()?->ownsEvent($event)) {
            return false;
        }

        if (! $this->user()?->isPlatformAdmin() && ! $this->user()?->organiserProfile) {
            return false;
        }

        return $this->isMethod('post')
            ? ($this->user()?->hasPermission('events.create') ?? false)
            : ($this->user()?->hasPermission('events.update') ?? false);
    }

    protected function prepareForValidation(): void
    {
        if ($this->user() && ! $this->user()->isPlatformAdmin()) {
            $this->merge([
                'organiser_profile_id' => $this->user()->organiserProfile?->id,
            ]);
        }
    }

    public function rules(): array
    {
        $eventId = $this->route('event')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'organiser_profile_id' => ['required', 'integer', Rule::exists('organiser_profiles', 'id')->where('status', 'active')],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('events', 'slug')->ignore($eventId)],
            'custom_url' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('events', 'custom_url')->ignore($eventId)],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'allow_duplicate_email_registration' => ['nullable', 'boolean'],
            'status_key' => ['required', Rule::in(['draft', 'submitted', 'published'])],
            'registration_opens_at' => ['nullable', 'date'],
            'registration_closes_at' => ['nullable', 'date', 'after_or_equal:registration_opens_at'],
        ];
    }
}
