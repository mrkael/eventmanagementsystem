<?php

namespace App\Http\Requests\Core;

use App\Models\RegistrationFormField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RegistrationFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('registration_forms.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'ticket_ids' => ['required', 'array', 'min:1'],
            'ticket_ids.*' => ['integer'],
            'fields_payload' => ['required', 'json'],
            'custom_questions_payload' => ['nullable', 'json'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $fields = json_decode((string) $this->input('fields_payload'), true);
                $event = $this->route('event');
                $ticketIds = collect($this->input('ticket_ids', []))->filter()->map(fn ($id) => (int) $id)->values();

                if ($event && $ticketIds->isNotEmpty()) {
                    $validTickets = $event->tickets()->whereIn('id', $ticketIds)->count();
                    if ($validTickets !== $ticketIds->count()) {
                        $validator->errors()->add('ticket_ids', 'Selected tickets must belong to this event.');
                    }
                }

                if (! is_array($fields) || count($fields) === 0) {
                    $validator->errors()->add('fields_payload', 'Add at least one form field.');

                    return;
                }

                foreach ($fields as $index => $field) {
                    $label = trim((string) ($field['label'] ?? ''));
                    $type = (string) ($field['type'] ?? '');
                    $options = $field['options'] ?? [];

                    if ($label === '') {
                        $validator->errors()->add("fields.{$index}.label", 'Question name is required.');
                    }

                    if (! in_array($type, RegistrationFormField::TYPES, true)) {
                        $validator->errors()->add("fields.{$index}.type", 'Question type is invalid.');
                    }

                    if (in_array($type, ['dropdown', 'radio', 'checkbox'], true) && (! is_array($options) || count(array_filter($options)) === 0)) {
                        $validator->errors()->add("fields.{$index}.options", 'Options are required for dropdown, radio, and checkbox fields.');
                    }
                }

                foreach ($this->customQuestions() as $index => $question) {
                    $label = trim((string) ($question['question_name'] ?? $question['label'] ?? ''));
                    $type = (string) ($question['type'] ?? '');
                    $options = $question['options'] ?? [];

                    if ($label === '') {
                        $validator->errors()->add("custom_questions.{$index}.question_name", 'Question name is required.');
                    }

                    if (! in_array($type, RegistrationFormField::TYPES, true)) {
                        $validator->errors()->add("custom_questions.{$index}.type", 'Question type is invalid.');
                    }

                    if (in_array($type, ['dropdown', 'radio', 'checkbox'], true) && (! is_array($options) || count(array_filter($options)) === 0)) {
                        $validator->errors()->add("custom_questions.{$index}.options", 'Options are required for dropdown, radio, and checkbox questions.');
                    }
                }
            },
        ];
    }

    public function fields(): array
    {
        return json_decode((string) $this->input('fields_payload'), true) ?: [];
    }

    public function customQuestions(): array
    {
        return json_decode((string) $this->input('custom_questions_payload'), true) ?: [];
    }
}
