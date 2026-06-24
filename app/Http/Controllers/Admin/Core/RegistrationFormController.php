<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\RegistrationFormRequest;
use App\Models\Event;
use App\Models\RegistrationForm;
use App\Models\RegistrationFormField;
use App\Services\AuditLogger;
use App\Services\Core\RegistrationFormBuilderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistrationFormController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.core.forms.index', [
            'event' => $event,
            'forms' => $event->registrationForms()->with(['tickets'])->withCount('fields')->latest()->paginate(10),
        ]);
    }

    public function create(Event $event): View
    {
        return $this->builderView($event, new RegistrationForm(['status' => 'active']), 'admin.core.forms.create');
    }

    public function store(RegistrationFormRequest $request, Event $event, RegistrationFormBuilderService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $form = $service->save($event, null, $request->validated(), $request->fields(), $request->customQuestions(), $request->user()->id);
        $auditLogger->record('forms.create', "Created registration form {$form->title}.", $form);

        return redirect()->route('core.events.forms.index', $event)->with('status', 'Registration form saved.');
    }

    public function edit(Event $event, RegistrationForm $form): View
    {
        abort_unless($form->event_id === $event->id, 404);

        return $this->builderView($event, $form->load('fields', 'tickets'), 'admin.core.forms.edit');
    }

    public function update(RegistrationFormRequest $request, Event $event, RegistrationForm $form, RegistrationFormBuilderService $service, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($form->event_id === $event->id, 404);

        $form = $service->save($event, $form, $request->validated(), $request->fields(), $request->customQuestions(), $request->user()->id);
        $auditLogger->record('forms.update', "Updated registration form {$form->title}.", $form);

        return redirect()->route('core.events.forms.index', $event)->with('status', 'Registration form updated.');
    }

    public function preview(Event $event, RegistrationForm $form): View
    {
        abort_unless($form->event_id === $event->id, 404);

        return view('admin.core.forms.preview', [
            'event' => $event,
            'form' => $form->load('fields', 'tickets'),
        ]);
    }

    public function destroy(Event $event, RegistrationForm $form, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($form->event_id === $event->id, 404);

        $event->tickets()->where('registration_form_id', $form->id)->update(['registration_form_id' => null]);
        $auditLogger->record('forms.delete', "Deleted registration form {$form->title}.", $form);
        $form->delete();

        return redirect()->route('core.events.forms.index', $event)->with('status', 'Registration form deleted.');
    }

    private function builderView(Event $event, RegistrationForm $form, string $view): View
    {
        $basicFields = [
            ['source_type' => 'basic', 'field_key' => 'full_name', 'label' => 'Full Name', 'type' => 'text', 'placeholder' => 'Full name', 'error_text' => 'Full name is required.', 'is_required' => true, 'options' => []],
            ['source_type' => 'basic', 'field_key' => 'email', 'label' => 'Email', 'type' => 'email', 'placeholder' => 'Email address', 'error_text' => 'A valid email is required.', 'is_required' => true, 'options' => []],
            ['source_type' => 'basic', 'field_key' => 'phone_number', 'label' => 'Phone Number', 'type' => 'text', 'placeholder' => 'Phone number', 'error_text' => 'Phone number is required.', 'is_required' => false, 'options' => []],
            ['source_type' => 'basic', 'field_key' => 'organization', 'label' => 'Organization', 'type' => 'text', 'placeholder' => 'Organization', 'error_text' => 'Organization is required.', 'is_required' => false, 'options' => []],
            ['source_type' => 'basic', 'field_key' => 'designation', 'label' => 'Designation', 'type' => 'text', 'placeholder' => 'Designation', 'error_text' => 'Designation is required.', 'is_required' => false, 'options' => []],
        ];

        $availableTickets = $event->tickets()
            ->where(function ($query) use ($form) {
                $query->where(function ($query) {
                    $query->where('status', 'active')
                        ->where('is_hidden', false)
                        ->where('available_quantity', '>', 0);
                });

                if ($form->exists) {
                    $query->orWhere('registration_form_id', $form->id);
                }
            })
            ->orderBy('name')
            ->get();

        return view($view, [
            'event' => $event,
            'form' => $form,
            'tickets' => $availableTickets,
            'customQuestions' => $event->customQuestions()->latest()->get(),
            'fieldTypes' => RegistrationFormField::TYPES,
            'basicFields' => $basicFields,
            'builderFields' => $form->exists ? $form->fields->map(fn (RegistrationFormField $field) => [
                'source_type' => $field->source_type,
                'field_key' => $field->field_key ?: $field->key,
                'label' => $field->label,
                'type' => $field->type,
                'placeholder' => $field->placeholder,
                'error_text' => $field->error_text,
                'is_required' => $field->is_required,
                'options' => $field->options ?: [],
            ])->values()->all() : $basicFields,
        ]);
    }
}
