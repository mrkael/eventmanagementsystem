<?php

namespace App\Services\Registrations;

use App\Models\Event;
use App\Models\RegistrationForm;
use App\Models\RegistrationQuestion;
use Illuminate\Support\Str;

class RegistrationFormBuilderService
{
    public function save(Event $event, array $settings, string $schemaJson): RegistrationForm
    {
        $schema = $this->normalize($schemaJson);

        $form = RegistrationForm::updateOrCreate(
            ['event_id' => $event->id],
            [
                'title' => $settings['title'] ?: "{$event->title} Registration",
                'description' => $settings['description'] ?? null,
                'access_mode' => $settings['access_mode'] ?? 'public',
                'is_enabled' => (bool) ($settings['is_enabled'] ?? false),
                'requires_approval' => (bool) ($settings['requires_approval'] ?? false),
                'allow_waitlist' => (bool) ($settings['allow_waitlist'] ?? true),
                'is_multi_step' => (bool) ($settings['is_multi_step'] ?? false),
                'opens_at' => $settings['opens_at'] ?? null,
                'closes_at' => $settings['closes_at'] ?? null,
                'settings' => ['schema_version' => 1],
            ],
        );

        $form->groups()->delete();
        $form->questions()->delete();

        foreach ($schema['groups'] as $groupIndex => $groupData) {
            $group = $form->groups()->create([
                'title' => $groupData['title'],
                'description' => $groupData['description'] ?? null,
                'step_number' => $groupData['step_number'],
                'sort_order' => $groupIndex,
            ]);

            foreach ($groupData['questions'] as $questionIndex => $questionData) {
                $form->questions()->create([
                    'registration_question_group_id' => $group->id,
                    'type' => $questionData['type'],
                    'label' => $questionData['label'],
                    'key' => $questionData['key'],
                    'help_text' => $questionData['help_text'] ?? null,
                    'is_required' => $questionData['is_required'],
                    'options' => $questionData['options'],
                    'validation_rules' => $questionData['validation_rules'],
                    'conditional_logic' => $questionData['conditional_logic'],
                    'sort_order' => ($groupIndex * 100) + $questionIndex,
                ]);
            }
        }

        return $form->fresh(['groups.questions']);
    }

    public function normalize(?string $schemaJson): array
    {
        $decoded = json_decode($schemaJson ?: '', true);

        if (! is_array($decoded) || empty($decoded['groups'])) {
            $decoded = ['groups' => $this->defaultSchema()['groups']];
        }

        $usedKeys = [];

        $groups = collect($decoded['groups'])->map(function (array $group, int $groupIndex) use (&$usedKeys) {
            $questions = collect($group['questions'] ?? [])->map(function (array $question, int $questionIndex) use (&$usedKeys) {
                $type = in_array($question['type'] ?? '', RegistrationQuestion::TYPES, true) ? $question['type'] : 'text';
                $label = Str::limit(trim((string) ($question['label'] ?? 'Question')), 120, '');
                $key = Str::slug($question['key'] ?? $label, '_') ?: 'question';

                while (in_array($key, $usedKeys, true)) {
                    $key .= '_'.Str::lower(Str::random(4));
                }

                $usedKeys[] = $key;

                return [
                    'type' => $type,
                    'label' => $label,
                    'key' => $key,
                    'help_text' => Str::limit(trim((string) ($question['help_text'] ?? '')), 240, ''),
                    'is_required' => (bool) ($question['is_required'] ?? false),
                    'options' => $this->normalizeOptions($question['options'] ?? []),
                    'validation_rules' => is_array($question['validation_rules'] ?? null) ? $question['validation_rules'] : [],
                    'conditional_logic' => is_array($question['conditional_logic'] ?? null) ? $question['conditional_logic'] : [],
                    'sort_order' => $questionIndex,
                ];
            })->filter(fn (array $question) => filled($question['label']))->values()->all();

            return [
                'title' => Str::limit(trim((string) ($group['title'] ?? 'Participant Details')), 120, ''),
                'description' => Str::limit(trim((string) ($group['description'] ?? '')), 240, ''),
                'step_number' => max(1, (int) ($group['step_number'] ?? ($groupIndex + 1))),
                'sort_order' => $groupIndex,
                'questions' => $questions,
            ];
        })->filter(fn (array $group) => count($group['questions']) > 0)->values()->all();

        return ['groups' => $groups ?: $this->defaultSchema()['groups']];
    }

    public function defaultSchema(): array
    {
        return [
            'groups' => [
                [
                    'title' => 'Participant Details',
                    'description' => 'Core information required for registration.',
                    'step_number' => 1,
                    'questions' => [
                        ['type' => 'text', 'label' => 'Full Name', 'key' => 'full_name', 'is_required' => true, 'options' => [], 'validation_rules' => [], 'conditional_logic' => []],
                        ['type' => 'email', 'label' => 'Email Address', 'key' => 'email_address', 'is_required' => true, 'options' => [], 'validation_rules' => [], 'conditional_logic' => []],
                        ['type' => 'text', 'label' => 'Organization', 'key' => 'organization', 'is_required' => false, 'options' => [], 'validation_rules' => [], 'conditional_logic' => []],
                    ],
                ],
            ],
        ];
    }

    private function normalizeOptions(array|string|null $options): array
    {
        if (is_string($options)) {
            $options = preg_split('/\r\n|\r|\n/', $options) ?: [];
        }

        return collect($options ?: [])
            ->map(fn ($option) => Str::limit(trim((string) $option), 80, ''))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
