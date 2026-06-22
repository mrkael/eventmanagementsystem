<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\Events\PageBuilderService;
use Illuminate\View\View;

class EventPageController extends Controller
{
    public function show(Event $event): View
    {
        abort_unless($event->is_public && $event->publishedPageVersion, 404);

        return view('public.events.show', [
            'event' => $event->load('venue', 'sessions', 'documents', 'publishedPageVersion'),
            'sections' => $event->publishedPageVersion?->sections ?: app(PageBuilderService::class)->defaultSections($event),
            'preview' => false,
        ]);
    }
}
