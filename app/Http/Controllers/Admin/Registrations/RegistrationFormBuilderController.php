<?php

namespace App\Http\Controllers\Admin\Registrations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registrations\RegistrationFormBuilderRequest;
use App\Models\Event;
use App\Services\AuditLogger;
use App\Services\Registrations\RegistrationFormBuilderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistrationFormBuilderController extends Controller
{
    public function edit(Event $event, RegistrationFormBuilderService $builder): View
    {
        abort_unless(auth()->user()->hasPermission('registration_forms.manage') || (auth()->user()->hasPermission('events.update') && $event->organizer_id === auth()->id()), 403);

        $form = $event->registrationForm()->with('groups.questions')->first();

        return view('admin.registrations.builder', [
            'event' => $event,
            'form' => $form,
            'schema' => $form ? $this->schemaFromForm($form) : $builder->defaultSchema(),
        ]);
    }

    public function update(RegistrationFormBuilderRequest $request, Event $event, RegistrationFormBuilderService $builder, AuditLogger $auditLogger): RedirectResponse
    {
        $form = $builder->save($event, $request->validated(), $request->string('schema'));
        $auditLogger->record('registration_forms.update', "Updated registration form for {$event->title}.", $form, [], $form->toArray());

        return redirect()->route('admin.events.registrations.builder.edit', $event)->with('status', 'Registration form saved successfully.');
    }

    private function schemaFromForm($form): array
    {
        return [
            'groups' => $form->groups->map(fn ($group) => [
                'title' => $group->title,
                'description' => $group->description,
                'step_number' => $group->step_number,
                'questions' => $group->questions->map(fn ($question) => [
                    'type' => $question->type,
                    'label' => $question->label,
                    'key' => $question->key,
                    'help_text' => $question->help_text,
                    'is_required' => $question->is_required,
                    'options' => $question->options ?: [],
                    'validation_rules' => $question->validation_rules ?: [],
                    'conditional_logic' => $question->conditional_logic ?: [],
                ])->values()->all(),
            ])->values()->all(),
        ];
    }
}
