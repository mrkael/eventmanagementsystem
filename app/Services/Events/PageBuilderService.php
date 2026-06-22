<?php

namespace App\Services\Events;

use App\Models\Event;
use App\Models\EventPageVersion;
use App\Models\User;

class PageBuilderService
{
    public const TYPES = ['hero', 'about', 'agenda', 'gallery', 'venue', 'faq', 'registration_cta'];

    public function normalize(?string $json): array
    {
        $decoded = json_decode($json ?: '[]', true);
        $sections = is_array($decoded) ? $decoded : [];

        return collect($sections)->values()->map(function (array $section, int $index) {
            $type = in_array($section['type'] ?? '', self::TYPES, true) ? $section['type'] : 'about';

            return [
                'id' => $section['id'] ?? (string) str()->uuid(),
                'type' => $type,
                'title' => str((string) ($section['title'] ?? ucfirst(str_replace('_', ' ', $type))))->limit(120)->toString(),
                'content' => str((string) ($section['content'] ?? ''))->limit(5000)->toString(),
                'sort_order' => $index,
                'settings' => is_array($section['settings'] ?? null) ? $section['settings'] : [],
            ];
        })->all();
    }

    public function saveDraft(Event $event, array $sections): EventPageVersion
    {
        $version = $event->pageVersions()->max('version') ?: 0;

        return $event->pageVersions()->create([
            'version' => $version + 1,
            'status' => 'draft',
            'sections' => $sections,
        ]);
    }

    public function publish(Event $event, User $user, ?EventPageVersion $version = null): EventPageVersion
    {
        $version ??= $event->pageVersions()->latest('version')->first();

        if (! $version) {
            $version = $this->saveDraft($event, $this->defaultSections($event));
        }

        $event->pageVersions()->where('status', 'published')->update(['status' => 'archived']);
        $version->update(['status' => 'published', 'published_by' => $user->id, 'published_at' => now()]);
        $event->update(['published_page_version_id' => $version->id]);

        return $version;
    }

    public function defaultSections(Event $event): array
    {
        return [
            ['id' => (string) str()->uuid(), 'type' => 'hero', 'title' => $event->title, 'content' => $event->summary ?? '', 'sort_order' => 0, 'settings' => []],
            ['id' => (string) str()->uuid(), 'type' => 'about', 'title' => 'About Event', 'content' => $event->description ?? '', 'sort_order' => 1, 'settings' => []],
            ['id' => (string) str()->uuid(), 'type' => 'registration_cta', 'title' => 'Register Now', 'content' => 'Registration details will be available soon.', 'sort_order' => 2, 'settings' => []],
        ];
    }
}
