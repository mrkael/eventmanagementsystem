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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'min_quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'max_quantity' => ['required', 'integer', 'gte:min_quantity', 'lte:quantity', 'max:1000000'],
            'sales_start_at' => ['required', 'date'],
            'sales_end_at' => ['required', 'date', 'after_or_equal:sales_start_at'],
            'is_hidden' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
