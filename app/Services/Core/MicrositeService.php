<?php

namespace App\Services\Core;

use App\Models\Event;
use App\Models\EventPage;
use Illuminate\Support\Str;

class MicrositeService
{
    public const SECTION_TYPES = ['hero', 'description', 'agenda', 'venue', 'faq', 'sponsors', 'registration_cta'];

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
            $type = in_array($section['type'] ?? '', self::SECTION_TYPES, true) ? $section['type'] : 'description';

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
            ['type' => 'hero', 'title' => $event->title, 'content' => $event->summary ?? ''],
            ['type' => 'description', 'title' => 'About This Event', 'content' => $event->description ?? ''],
            ['type' => 'agenda', 'title' => 'Agenda', 'content' => 'Session details will be updated soon.'],
            ['type' => 'venue', 'title' => 'Venue', 'content' => $event->location ?? 'Venue details coming soon.'],
            ['type' => 'registration_cta', 'title' => 'Register Now', 'content' => 'Select an available ticket to register.'],
        ];
    }
}
