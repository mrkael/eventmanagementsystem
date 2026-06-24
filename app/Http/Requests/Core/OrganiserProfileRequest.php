<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganiserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->isMethod('post')
            ? ($this->user()?->hasPermission('organisers.create') ?? false)
            : ($this->user()?->hasPermission('organisers.update') ?? false);
    }

    public function rules(): array
    {
        $organiser = $this->route('organiser');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('organiser_profiles', 'email')->ignore($organiser?->id)->whereNull('deleted_at')],
            'phone' => ['nullable', 'string', 'max:80'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:3000'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
