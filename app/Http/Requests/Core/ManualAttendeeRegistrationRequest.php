<?php

namespace App\Http\Requests\Core;

use App\Models\Registration;
use App\Models\RegistrationFormField;
use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManualAttendeeRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $registration = $this->route('registration');
        $ticket = $this->route('ticket');
        if (! $ticket instanceof Ticket && $registration instanceof Registration) {
            $ticket = $registration->ticket;
        }
        $form = $ticket instanceof Ticket ? $ticket->form?->loadMissing('fields') : null;
        $fields = $form?->fields ?? collect();
        $fieldKeys = $fields->pluck('key')->all();

        $rules = [
            'full_name' => [in_array('full_name', $fieldKeys, true) ? 'nullable' : 'required', 'string', 'max:255'],
            'email' => [in_array('email', $fieldKeys, true) ? 'nullable' : 'required', 'email:rfc', 'max:255'],
            'answers' => ['nullable', 'array'],
            'answer_files' => ['nullable', 'array'],
        ];

        foreach ($fields as $field) {
            $key = "answers.{$field->key}";

            if ($field->type === 'file') {
                $hasExistingFile = $registration instanceof Registration
                    && $registration->answers()->where('field_key', $field->key)->whereNotNull('file_path')->exists();
                $rules["answer_files.{$field->key}"] = array_merge($field->is_required && ! $hasExistingFile ? ['required'] : ['nullable'], ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx']);

                continue;
            }

            if ($field->type === 'checkbox') {
                $rules[$key] = $field->is_required ? ['required', 'array', 'min:1'] : ['nullable', 'array'];
                $rules["{$key}.*"] = $this->optionValueRules($field);

                continue;
            }

            $rules[$key] = array_merge($field->is_required ? ['required'] : ['nullable'], $this->fieldRules($field));
        }

        return $rules;
    }

    public function payload(): array
    {
        $answers = $this->validated('answers', []);

        return [
            'full_name' => $answers['full_name'] ?? $this->validated('full_name'),
            'email' => $answers['email'] ?? $this->validated('email'),
            'phone' => $answers['phone_number'] ?? $answers['phone'] ?? null,
            'organization' => $answers['organization'] ?? null,
            'designation' => $answers['designation'] ?? null,
            'answers' => $answers,
            'answer_files' => $this->file('answer_files', []),
        ];
    }

    private function fieldRules(RegistrationFormField $field): array
    {
        return match ($field->type) {
            'email' => ['email:rfc', 'max:255'],
            'number' => ['numeric'],
            'date' => ['date'],
            'dropdown', 'radio' => $this->optionRules($field),
            'textarea' => ['string', 'max:5000'],
            default => ['string', 'max:255'],
        };
    }

    private function optionRules(RegistrationFormField $field): array
    {
        $options = collect($field->options ?: [])->map(fn ($option) => (string) $option)->all();

        return $options === [] ? [] : [Rule::in($options)];
    }

    private function optionValueRules(RegistrationFormField $field): array
    {
        $options = collect($field->options ?: [])->map(fn ($option) => (string) $option)->all();

        return $options === [] ? ['string', 'max:255'] : [Rule::in($options)];
    }
}
