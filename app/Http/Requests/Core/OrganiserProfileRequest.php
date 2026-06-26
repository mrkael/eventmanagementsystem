<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganiserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organiser = $this->route('organiser');

        if ($this->isMethod('post')) {
            return ($this->user()?->isPlatformAdmin() ?? false)
                && ($this->user()?->hasPermission('organisers.create') ?? false);
        }

        return ($this->user()?->hasPermission('organisers.update') ?? false)
            && (($this->user()?->isPlatformAdmin() ?? false) || $organiser?->user_id === $this->user()?->id);
    }

    public function rules(): array
    {
        $organiser = $this->route('organiser');
        $emailRules = ['required', 'email:rfc', 'max:255', Rule::unique('organiser_profiles', 'email')->ignore($organiser?->id)->whereNull('deleted_at')];

        if (! $this->isMethod('post')) {
            $emailRules[] = Rule::unique('users', 'email')->ignore($organiser?->user_id);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'phone' => ['nullable', 'string', 'max:80'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:3000'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
