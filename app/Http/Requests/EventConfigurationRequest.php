<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission($this->route('eventConfiguration') ? 'event_configurations.update' : 'event_configurations.create') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('eventConfiguration')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('event_configurations', 'name')->ignore($id)],
            'is_default' => ['nullable', 'boolean'],
            'registration_requires_approval' => ['nullable', 'boolean'],
            'registration_allow_waitlist' => ['nullable', 'boolean'],
            'registration_private_by_default' => ['nullable', 'boolean'],
            'registration_open_days_before' => ['required', 'integer', 'min:0', 'max:365'],
            'qr_enabled' => ['nullable', 'boolean'],
            'qr_expires_after_event_hours' => ['required', 'integer', 'min:0', 'max:8760'],
            'qr_allow_reuse' => ['nullable', 'boolean'],
            'capacity_enforce_limit' => ['nullable', 'boolean'],
            'capacity_waitlist_when_full' => ['nullable', 'boolean'],
            'capacity_overbooking_limit' => ['required', 'integer', 'min:0', 'max:1000000'],
            'email_send_confirmation' => ['nullable', 'boolean'],
            'email_send_reminder' => ['nullable', 'boolean'],
            'email_reminder_hours_before' => ['required', 'integer', 'min:0', 'max:8760'],
        ];
    }
}
