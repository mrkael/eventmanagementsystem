<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;

class PageBuilderRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $pageSections = $this->input('page_sections');

        if (is_array($pageSections)) {
            $this->merge(['page_sections' => json_encode($pageSections)]);
        }

        if ($pageSections === 'Array' || blank($pageSections)) {
            $this->merge(['page_sections' => '[]']);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('event')) ?? false;
    }

    public function rules(): array
    {
        return ['page_sections' => ['required', 'json']];
    }
}
