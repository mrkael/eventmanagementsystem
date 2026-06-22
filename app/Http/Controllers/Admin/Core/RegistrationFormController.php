<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\RegistrationForm;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegistrationFormController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.core.forms.index', [
            'event' => $event,
            'forms' => $event->registrationForms()->withCount('fields', 'tickets')->latest()->paginate(10),
            'fieldTypes' => ['text', 'textarea', 'email', 'number', 'dropdown', 'radio', 'checkbox', 'date', 'file'],
        ]);
    }

    public function store(Request $request, Event $event, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $this->validated($request);
        $form = $event->registrationForms()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'access_mode' => 'public',
            'is_enabled' => true,
            'requires_approval' => false,
            'allow_waitlist' => false,
            'is_multi_step' => (bool) ($data['is_multi_step'] ?? false),
        ]);
        $this->syncFields($form, $data['fields'] ?? []);
        $auditLogger->record('core.forms.create', "Created registration form {$form->title}.", $form);

        return back()->with('status', 'Registration form saved.');
    }

    public function update(Request $request, Event $event, RegistrationForm $form, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($form->event_id === $event->id, 404);
        $data = $this->validated($request);
        $form->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_multi_step' => (bool) ($data['is_multi_step'] ?? false),
        ]);
        $this->syncFields($form, $data['fields'] ?? []);
        $auditLogger->record('core.forms.update', "Updated registration form {$form->title}.", $form);

        return back()->with('status', 'Registration form updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_multi_step' => ['nullable', 'boolean'],
            'fields' => ['nullable', 'array'],
            'fields.*.label' => ['required_with:fields', 'string', 'max:160'],
            'fields.*.type' => ['required_with:fields', 'in:text,textarea,email,number,dropdown,radio,checkbox,date,file'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:160'],
            'fields.*.is_required' => ['nullable', 'boolean'],
            'fields.*.options' => ['nullable', 'string', 'max:1000'],
            'fields.*.validation_rules' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function syncFields(RegistrationForm $form, array $fields): void
    {
        $form->fields()->delete();
        foreach (array_values($fields) as $index => $field) {
            $label = trim($field['label'] ?? '');
            if ($label === '') {
                continue;
            }

            $form->fields()->create([
                'key' => Str::slug($label, '_').'_'.($index + 1),
                'label' => $label,
                'type' => $field['type'] ?? 'text',
                'placeholder' => $field['placeholder'] ?? null,
                'is_required' => (bool) ($field['is_required'] ?? false),
                'sort_order' => $index + 1,
                'options' => $this->options($field['options'] ?? null),
                'validation_rules' => ($field['validation_rules'] ?? null) ? collect(explode('|', $field['validation_rules']))->map(fn ($rule) => trim($rule))->filter()->values()->all() : null,
            ]);
        }
    }

    private function options(?string $options): ?array
    {
        if (! $options) {
            return null;
        }

        return collect(preg_split('/\r\n|\r|\n/', $options))->map(fn ($option) => trim($option))->filter()->values()->all();
    }
}
