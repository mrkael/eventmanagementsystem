<?php

namespace App\Services\Core;

use App\Models\Event;
use App\Models\RegistrationForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistrationFormBuilderService
{
    public function save(Event $event, ?RegistrationForm $form, array $data, array $fields, array $customQuestions, int $userId): RegistrationForm
    {
        return DB::transaction(function () use ($event, $form, $data, $fields, $customQuestions, $userId) {
            $form = $form ?: new RegistrationForm(['event_id' => $event->id, 'created_by' => $userId]);
            $form->fill([
                'title' => $data['title'],
                'description' => null,
                'status' => 'active',
                'access_mode' => 'public',
                'is_enabled' => true,
                'requires_approval' => false,
                'allow_waitlist' => false,
                'is_multi_step' => false,
                'updated_by' => $userId,
            ]);
            $form->save();

            $this->syncFields($form, $fields);
            $this->syncTickets($event, $form, $data['ticket_ids'] ?? []);
            $this->storeCustomQuestions($event, $customQuestions, $userId);

            return $form->fresh(['fields', 'tickets']);
        });
    }

    private function syncFields(RegistrationForm $form, array $fields): void
    {
        $form->fields()->delete();

        foreach (array_values($fields) as $index => $field) {
            $label = trim((string) ($field['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $fieldKey = $this->fieldKey($field, $label, $index);

            $form->fields()->create([
                'source_type' => $field['source_type'] ?? 'custom',
                'field_key' => $fieldKey,
                'key' => $fieldKey,
                'label' => $label,
                'type' => $field['type'] ?? 'text',
                'placeholder' => $field['placeholder'] ?? null,
                'error_text' => $field['error_text'] ?? null,
                'is_required' => (bool) ($field['is_required'] ?? false),
                'options' => $this->cleanOptions($field['options'] ?? []),
                'validation_rules' => $this->validationRules($field),
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function syncTickets(Event $event, RegistrationForm $form, array $ticketIds): void
    {
        $allowedTicketIds = $event->tickets()->whereIn('id', $ticketIds)->pluck('id')->all();
        $event->tickets()->where('registration_form_id', $form->id)->whereNotIn('id', $allowedTicketIds)->update(['registration_form_id' => null]);
        $event->tickets()->whereIn('id', $allowedTicketIds)->update(['registration_form_id' => $form->id]);
    }

    private function storeCustomQuestions(Event $event, array $customQuestions, int $userId): void
    {
        foreach ($customQuestions as $question) {
            $name = trim((string) ($question['question_name'] ?? ''));
            $type = (string) ($question['type'] ?? 'text');

            if ($name === '') {
                continue;
            }

            $event->customQuestions()->updateOrCreate(
                [
                    'question_name' => $name,
                    'type' => $type,
                ],
                [
                    'placeholder' => $question['placeholder'] ?? null,
                    'error_text' => $question['error_text'] ?? null,
                    'is_required' => (bool) ($question['is_required'] ?? false),
                    'options' => $this->cleanOptions($question['options'] ?? []),
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ],
            );
        }
    }

    private function fieldKey(array $field, string $label, int $index): string
    {
        $key = (string) ($field['field_key'] ?? '');

        return $key !== '' ? $key : Str::slug($label, '_').'_'.($index + 1);
    }

    private function cleanOptions(array|string|null $options): ?array
    {
        if (is_string($options)) {
            $options = preg_split('/\r\n|\r|\n/', $options) ?: [];
        }

        $clean = collect($options ?: [])->map(fn ($option) => trim((string) $option))->filter()->values()->all();

        return $clean === [] ? null : $clean;
    }

    private function validationRules(array $field): ?array
    {
        $rules = [];

        if ((bool) ($field['is_required'] ?? false)) {
            $rules[] = 'required';
        }

        if (($field['type'] ?? '') === 'email') {
            $rules[] = 'email';
        }

        if (($field['type'] ?? '') === 'number') {
            $rules[] = 'numeric';
        }

        return $rules === [] ? null : $rules;
    }
}
