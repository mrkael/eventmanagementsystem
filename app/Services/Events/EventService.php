<?php

namespace App\Services\Events;

use App\Enums\EventLifecycleStatus;
use App\Models\Event;
use App\Models\EventStatus;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventService
{
    public function __construct(private PageBuilderService $pageBuilder) {}

    public function save(array $data, User $user, ?Event $event = null): Event
    {
        return DB::transaction(function () use ($data, $user, $event) {
            $event ??= new Event(['organizer_id' => $user->id, 'status_key' => EventLifecycleStatus::Draft->value]);
            $event->fill($this->eventPayload($data, $event));

            if (isset($data['banner']) && $data['banner'] instanceof UploadedFile) {
                $event->banner_path = $data['banner']->store('events/banners', 'public');
            }

            $event->save();
            $this->syncSessions($event, $data['sessions'] ?? []);
            $this->storeDocuments($event, $data['documents'] ?? [], $user);

            if (! empty($data['page_sections'])) {
                $this->pageBuilder->saveDraft($event, $this->pageBuilder->normalize($data['page_sections']));
            } elseif (! $event->pageVersions()->exists()) {
                $this->pageBuilder->saveDraft($event, $this->pageBuilder->defaultSections($event));
            }

            return $event->fresh(['sessions', 'documents', 'pageVersions']);
        });
    }

    public function submit(Event $event): void
    {
        $event->update([
            'status_key' => EventLifecycleStatus::Submitted,
            'submitted_at' => now(),
        ]);
    }

    public function publish(Event $event, User $user): void
    {
        DB::transaction(function () use ($event, $user) {
            $publishedStatus = EventStatus::where('key', 'approved')->orWhere('key', 'published')->first();
            $this->pageBuilder->publish($event, $user);
            $event->update([
                'status_key' => EventLifecycleStatus::Published,
                'event_status_id' => $publishedStatus?->id ?? $event->event_status_id,
                'is_public' => true,
                'published_at' => now(),
            ]);
        });
    }

    private function eventPayload(array $data, Event $event): array
    {
        return [
            'event_category_id' => $data['event_category_id'],
            'event_type_id' => $data['event_type_id'],
            'venue_id' => $data['venue_id'] ?? null,
            'event_status_id' => $data['event_status_id'],
            'event_configuration_id' => $data['event_configuration_id'] ?? null,
            'title' => $data['title'],
            'slug' => ($data['slug'] ?? null) ?: Str::slug($data['title']).($event->exists ? '' : '-'.Str::lower(Str::random(5))),
            'summary' => $data['summary'] ?? null,
            'description' => $data['description'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'capacity' => $data['capacity'],
            'is_registration_enabled' => (bool) ($data['is_registration_enabled'] ?? false),
            'is_public' => (bool) ($data['is_public'] ?? false),
        ];
    }

    private function syncSessions(Event $event, array $sessions): void
    {
        $event->sessions()->delete();

        foreach (array_values($sessions) as $index => $session) {
            if (blank($session['title'] ?? null)) {
                continue;
            }

            $event->sessions()->create([
                'title' => $session['title'],
                'description' => $session['description'] ?? null,
                'starts_at' => $session['starts_at'] ?: $event->starts_at,
                'ends_at' => $session['ends_at'] ?: $event->ends_at,
                'venue_id' => $session['venue_id'] ?? null,
                'capacity' => $session['capacity'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }

    private function storeDocuments(Event $event, array $documents, User $user): void
    {
        foreach ($documents as $document) {
            if (! $document instanceof UploadedFile) {
                continue;
            }

            $event->documents()->create([
                'uploaded_by' => $user->id,
                'name' => $document->getClientOriginalName(),
                'path' => $document->store('events/documents', 'public'),
                'mime_type' => $document->getMimeType(),
                'size' => $document->getSize() ?: 0,
            ]);
        }
    }
}
