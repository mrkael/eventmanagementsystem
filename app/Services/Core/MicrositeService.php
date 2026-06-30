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

        $page = $event->pages()->where('status', 'draft')->latest()->first();

        if ($page) {
            $page->update(['template' => $data['template'] ?? 'default', 'settings' => $data['settings'] ?? []]);
        } else {
            $page = $event->pages()->create([
                'status' => 'draft',
                'template' => $data['template'] ?? 'default',
                'settings' => $data['settings'] ?? [],
            ]);
        }

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

        $newDraft = $event->pages()->create([
            'status' => 'draft',
            'template' => $draft->template,
            'settings' => $draft->settings ?? [],
        ]);
        foreach ($draft->sections as $section) {
            $newDraft->sections()->create([
                'type' => $section->type,
                'title' => $section->title,
                'content' => $section->content,
                'settings' => $section->settings,
                'sort_order' => $section->sort_order,
            ]);
        }

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
                'content' => $this->sanitizeContent((string) ($section['content'] ?? '')),
                'settings' => $this->sanitizeSettings(is_array($section['settings'] ?? null) ? $section['settings'] : []),
            ];
        })->values()->all();
    }

    private function sanitizeContent(string $content): string
    {
        $content = preg_replace('#<(script|style|iframe|object|embed)[^>]*>.*?</\1>#is', '', $content) ?? '';
        $content = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $content) ?? '';
        $content = preg_replace('/(href|src)\s*=\s*([\'"])\s*javascript:[^\'"]*\2/i', '$1="#"', $content) ?? '';
        $content = preg_replace_callback('/\sstyle\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', function (array $matches) {
            $style = trim($matches[1], '"\'');
            $safe = $this->sanitizeStyle($style);

            return $safe !== '' ? ' style="'.e($safe).'"' : '';
        }, $content) ?? '';
        $content = preg_replace('/\s(?!href\s*=|src\s*=|alt\s*=|title\s*=|target\s*=|rel\s*=|style\s*=|class\s*=|colspan\s*=|rowspan\s*=|scope\s*=|border\s*=|cellpadding\s*=|cellspacing\s*=|width\s*=|height\s*=)[a-z0-9:_-]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $content) ?? '';
        $content = strip_tags($content, '<p><br><strong><b><em><i><u><h1><h2><h3><h4><ul><ol><li><a><img><div><span><blockquote><table><thead><tbody><tfoot><tr><th><td><caption><col><colgroup>');

        return trim($content);
    }

    private function sanitizeStyle(string $style): string
    {
        $allowed = [
            'text-align' => '/^(left|center|right|justify)$/i',
            'float' => '/^(left|right|none)$/i',
            'display' => '/^(block|table|table-cell|table-row|inline-block)$/i',
            'border-collapse' => '/^(collapse|separate)$/i',
            'border-spacing' => '/^\d+(\.\d+)?(px|em|rem)(\s+\d+(\.\d+)?(px|em|rem))?$/i',
            'width' => '/^(auto|\d+(\.\d+)?(px|em|rem|%))$/i',
            'height' => '/^(auto|\d+(\.\d+)?(px|em|rem|%))$/i',
            'min-width' => '/^(auto|\d+(\.\d+)?(px|em|rem|%))$/i',
            'max-width' => '/^(auto|none|\d+(\.\d+)?(px|em|rem|%))$/i',
            'padding' => '/^(\d+(\.\d+)?(px|em|rem|%)\s*){1,4}$/i',
            'padding-top' => '/^\d+(\.\d+)?(px|em|rem|%)$/i',
            'padding-right' => '/^\d+(\.\d+)?(px|em|rem|%)$/i',
            'padding-bottom' => '/^\d+(\.\d+)?(px|em|rem|%)$/i',
            'padding-left' => '/^\d+(\.\d+)?(px|em|rem|%)$/i',
            'margin-left' => '/^(auto|\d+(\.\d+)?(px|em|rem|%))$/i',
            'margin-right' => '/^(auto|\d+(\.\d+)?(px|em|rem|%))$/i',
            'margin-top' => '/^\d+(\.\d+)?(px|em|rem|%)$/i',
            'margin-bottom' => '/^\d+(\.\d+)?(px|em|rem|%)$/i',
            'background-color' => '/^(#[0-9a-f]{3,8}|rgb\(\d{1,3},\s*\d{1,3},\s*\d{1,3}\)|rgba\(\d{1,3},\s*\d{1,3},\s*\d{1,3},\s*[\d.]+\)|[a-z]+)$/i',
            'color' => '/^(#[0-9a-f]{3,8}|rgb\(\d{1,3},\s*\d{1,3},\s*\d{1,3}\)|rgba\(\d{1,3},\s*\d{1,3},\s*\d{1,3},\s*[\d.]+\)|[a-z]+)$/i',
            'font-weight' => '/^(normal|bold|bolder|lighter|\d{3})$/i',
            'font-size' => '/^\d+(\.\d+)?(px|em|rem|pt|%)$/i',
            'vertical-align' => '/^(top|middle|bottom|baseline)$/i',
        ];

        $safe = [];
        foreach (array_filter(array_map('trim', explode(';', $style))) as $part) {
            $colon = strpos($part, ':');
            if ($colon === false) {
                continue;
            }
            $prop = strtolower(trim(substr($part, 0, $colon)));
            $val = trim(substr($part, $colon + 1));
            if (isset($allowed[$prop]) && preg_match($allowed[$prop], $val) && ! preg_match('/javascript:|expression\s*\(/i', $val)) {
                $safe[] = $prop.': '.$val;
            }
        }

        return $safe !== [] ? implode('; ', $safe).';' : '';
    }

    private function sanitizeSettings(array $settings): array
    {
        return collect($settings)->mapWithKeys(function ($value, $key) {
            if (is_string($value)) {
                $value = preg_replace('/javascript:/i', '', $value) ?? '';
                $value = strip_tags($value);
            }

            return [$key => $value];
        })->all();
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
