<?php

namespace App\Http\Requests;

use App\Enums\VenueStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class VenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission($this->route('venue') ? 'venues.update' : 'venues.create') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('venue')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('venues', 'name')->ignore($id)],
            'code' => ['required', 'string', 'max:50', Rule::unique('venues', 'code')->ignore($id)],
            'capacity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', new Enum(VenueStatus::class)],
        ];
    }
}
