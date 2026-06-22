<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('events.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'registration_form_id' => ['nullable', 'integer', 'exists:registration_forms,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'currency' => ['required', Rule::in(['MYR', 'IDR', 'SGD', 'USD'])],
            'min_quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'max_quantity' => ['required', 'integer', 'gte:min_quantity', 'max:1000000'],
            'is_hidden' => ['nullable', 'boolean'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'early_bird_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'group_min_quantity' => ['nullable', 'integer', 'min:2'],
            'group_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'sales_start_at' => ['nullable', 'date'],
            'sales_end_at' => ['nullable', 'date', 'after_or_equal:sales_start_at'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
