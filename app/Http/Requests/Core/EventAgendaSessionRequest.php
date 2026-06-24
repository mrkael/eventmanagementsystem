<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventAgendaSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('events.update') ?? false;
    }

    public function rules(): array
    {
        $event = $this->route('event');

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'session_type' => ['required', 'string', 'max:80'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'ticket_ids' => ['required', 'array', 'min:1'],
            'ticket_ids.*' => ['integer', Rule::exists('tickets', 'id')->where('event_id', $event?->id)],
        ];
    }

    public function sessionData(): array
    {
        $data = $this->validated();

        return [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'session_type' => $data['session_type'],
            'capacity' => $data['capacity'] ?? null,
            'venue_name' => $data['venue_name'] ?? null,
            'location' => $data['venue_name'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'status' => 'active',
        ];
    }
}
