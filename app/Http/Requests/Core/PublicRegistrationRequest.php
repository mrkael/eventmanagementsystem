<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;

class PublicRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'participants' => ['nullable', 'array'],
            'participants.*' => ['array'],
            'full_name' => ['required_without:participants', 'string', 'max:255'],
            'email' => ['required_without:participants', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'organization' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'promo_code' => ['nullable', 'string', 'max:60'],
            'answers' => ['nullable', 'array'],
            'answer_files' => ['nullable', 'array'],
            'answer_files.*' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ];
    }
}
