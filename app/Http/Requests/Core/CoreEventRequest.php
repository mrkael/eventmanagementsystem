<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('events.create') || $this->user()?->hasPermission('events.update');
    }

    public function rules(): array
    {
        $eventId = $this->route('event')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'organiser_profile_id' => ['nullable', 'integer', 'exists:organiser_profiles,id'],
            'custom_url' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('events', 'custom_url')->ignore($eventId)],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'payment_tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'allow_promo_code' => ['nullable', 'boolean'],
            'allow_duplicate_email' => ['nullable', 'boolean'],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'sender_email' => ['nullable', 'email:rfc', 'max:255'],
            'status_key' => ['required', Rule::in(['draft', 'published'])],
            'brand_color' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'banner' => ['nullable', 'image', 'max:4096'],
            'registration_opens_at' => ['nullable', 'date'],
            'registration_closes_at' => ['nullable', 'date', 'after_or_equal:registration_opens_at'],
        ];
    }
}
