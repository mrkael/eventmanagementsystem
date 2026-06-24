<?php

namespace App\Services\Core;

use App\Models\Event;
use App\Models\EventPage;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MicrositeService
{
    public const SECTION_TYPES = [
        'hero' => 'Banner',
        'text_content' => 'Text Content',
        'image' => 'Image',
        'button_cta' => 'Button / CTA',
        'agenda' => 'Agenda',
        'venue' => 'Venue',
        'faq' => 'FAQ',
        'sponsors' => 'Sponsor Logo',
        'ticket_selection' => 'Ticket Selection',
        'registration_form' => 'Registration Form',
        'footer' => 'Footer',
    ];

    public function save(Event $event, array $data): EventPage
    {
        $sections = $this->normalizeSections($data['sections'] ?? []);
        $page = EventPage::updateOrCreate(
            ['event_id' => $event->id, 'status' => 'draft'],
            ['template' => $data['template'] ?? 'default', 'settings' => $data['settings'] ?? []],
        );

        $page->sections()->delete();
        foreach ($sections as $index => $section) {
            $page->sections()->create([...$section, 'sort_order' => $index]);
        }

        return $page->fresh('sections');
    }

    public function publish(Event $event): EventPage
    {
        $draft = $event->pages()->with('sections')->where('status', 'draft')->latest()->first();
        if (! $draft) {
            $draft = $this->save($event, ['sections' => $this->defaultSections($event)]);
        }

        $this->validatePublishable($event, $draft);

        $event->pages()->where('status', 'published')->update(['status' => 'archived']);
        $draft->update(['status' => 'published', 'published_at' => now()]);
        $event->update(['status_key' => 'published', 'is_public' => true, 'published_at' => now()]);

        return $draft->fresh('sections');
    }

    public function normalizeSections(array|string|null $sections): array
    {
        if (is_string($sections)) {
            $sections = json_decode($sections, true) ?: [];
        }

        return collect($sections ?: [])->map(function (array $section) {
            $type = array_key_exists($section['type'] ?? '', self::SECTION_TYPES) ? $section['type'] : 'text_content';

            return [
                'type' => $type,
                'title' => Str::limit((string) ($section['title'] ?? ucfirst(str_replace('_', ' ', $type))), 255, ''),
                'content' => (string) ($section['content'] ?? ''),
                'settings' => is_array($section['settings'] ?? null) ? $section['settings'] : [],
            ];
        })->values()->all();
    }

    public function defaultSections(Event $event): array
    {
        return [
            ['type' => 'hero', 'title' => $event->title, 'content' => $event->description ?: 'Join us for this event.'],
            ['type' => 'ticket_selection', 'title' => 'Ticket & Form', 'content' => 'Choose your ticket. The linked registration form will appear on this page.'],
            ['type' => 'registration_form', 'title' => 'Registration Form', 'content' => 'The form shown here follows the selected ticket.'],
            ['type' => 'footer', 'title' => $event->title, 'content' => $event->location ?: 'Event details will be updated soon.'],
        ];
    }

    public function visibleTickets(Event $event): Collection
    {
        return $this->activeVisibleTickets($event)
            ->filter(fn (Ticket $ticket) => $ticket->form && $ticket->form->fields->isNotEmpty())
            ->values();
    }

    public function activeVisibleTickets(Event $event): Collection
    {
        return $event->tickets()
            ->with('form.fields')
            ->where('status', 'active')
            ->where('is_hidden', false)
            ->where('available_quantity', '>', 0)
            ->orderBy('name')
            ->get();
    }

    public function validatePublishable(Event $event, EventPage $page): void
    {
        $sections = $page->sections;
        $visibleTickets = $this->activeVisibleTickets($event);
        $errors = [];

        if (blank($page->template)) {
            $errors[] = 'Template name is required.';
        }

        if ($sections->isEmpty()) {
            $errors[] = 'Add at least one row before publishing the site.';
        }

        if (! $sections->contains('type', 'ticket_selection')) {
            $errors[] = 'Add the required Ticket Selection block before publishing.';
        }

        if (! $sections->contains('type', 'registration_form')) {
            $errors[] = 'Add the required Registration Form block before publishing.';
        }

        if ($visibleTickets->isEmpty()) {
            $errors[] = 'At least one active visible ticket with available quantity is required before publishing.';
        }

        $ticketsWithoutForms = $visibleTickets->filter(fn (Ticket $ticket) => ! $ticket->form);
        if ($ticketsWithoutForms->isNotEmpty()) {
            $errors[] = 'Every visible active ticket must have an assigned registration form.';
        }

        $ticketsWithEmptyForms = $visibleTickets->filter(fn (Ticket $ticket) => $ticket->form && $ticket->form->fields->isEmpty());
        if ($ticketsWithEmptyForms->isNotEmpty()) {
            $errors[] = 'Each assigned registration form must contain at least one field.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['site' => $errors]);
        }
    }
}
