<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $pageSections = $this->input('page_sections');

        if (is_array($pageSections)) {
            $this->merge(['page_sections' => json_encode($pageSections)]);
        }

        if ($pageSections === 'Array') {
            $this->merge(['page_sections' => null]);
        }
    }

    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event
            ? $this->user()?->can('update', $event) ?? false
            : $this->user()?->can('create', Event::class) ?? false;
    }

    public function rules(): array
    {
        $eventId = $this->route('event')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('events', 'slug')->ignore($eventId)],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'event_category_id' => ['required', 'integer', 'exists:event_categories,id'],
            'event_type_id' => ['required', 'integer', 'exists:event_types,id'],
            'venue_id' => ['nullable', 'integer', 'exists:venues,id'],
            'event_status_id' => ['required', 'integer', 'exists:event_statuses,id'],
            'event_configuration_id' => ['nullable', 'integer', 'exists:event_configurations,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_registration_enabled' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
            'publish_now' => ['nullable', 'boolean'],
            'banner' => ['nullable', 'image', 'max:4096'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg'],
            'sessions' => ['required', 'array', 'min:1'],
            'sessions.*.title' => ['required', 'string', 'max:255'],
            'sessions.*.description' => ['nullable', 'string', 'max:1000'],
            'sessions.*.starts_at' => ['required', 'date'],
            'sessions.*.ends_at' => ['required', 'date', 'after:sessions.*.starts_at'],
            'sessions.*.venue_id' => ['nullable', 'integer', 'exists:venues,id'],
            'sessions.*.capacity' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'page_sections' => ['nullable', 'json'],
        ];
    }
}
