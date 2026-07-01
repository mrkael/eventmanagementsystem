<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isPlatformAdmin = $user->isPlatformAdmin();

        $events = $this->scopedEvents($request)
            ->orderByRaw("FIELD(status_key,'published','submitted','draft')")
            ->orderBy('starts_at', 'desc')
            ->get(['id', 'title', 'status_key', 'starts_at', 'ends_at', 'capacity', 'organiser_profile_id']);

        $selectedEvent = $events->firstWhere('id', $request->integer('event_id'))
            ?? $events->first();

        $registrationCounts = $this->registrationCounts($events->pluck('id')->all());

        return view('admin.core.dashboard', [
            'events' => $events,
            'selectedEvent' => $selectedEvent,
            'stats' => $selectedEvent ? $this->computeStats($selectedEvent) : $this->emptyStats(),
            'recentRegistrations' => $selectedEvent ? $this->recentRegistrations($selectedEvent) : collect(),
            'registrationCounts' => $registrationCounts,
            'isLiveToday' => $selectedEvent ? $this->isLiveToday($selectedEvent) : false,
            'isPlatformAdmin' => $isPlatformAdmin,
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $event = Event::findOrFail($request->integer('event_id'));

        if (! $user->isPlatformAdmin() && $event->organiser_profile_id !== $user->organiserProfile?->id) {
            abort(403);
        }

        return response()->json([
            'stats' => $this->computeStats($event),
            'is_live_today' => $this->isLiveToday($event),
            'check_in_url' => route('core.events.check-in.index', $event),
            'event_url' => route('core.events.show', $event),
            'recent_registrations' => $this->recentRegistrationsPayload($event),
        ]);
    }

    private function scopedEvents(Request $request)
    {
        $user = $request->user();

        return Event::query()->when(
            ! $user->isPlatformAdmin(),
            fn ($q) => $q->where('organiser_profile_id', $user->organiserProfile?->id ?? 0),
        );
    }

    private function computeStats(Event $event): array
    {
        $base = Registration::where('event_id', $event->id)
            ->whereIn('status', ['confirmed', 'registered']);

        return [
            'total_registrations' => (clone $base)->count(),
            'this_week' => (clone $base)->where('created_at', '>=', now()->startOfWeek())->count(),
            'total_check_ins' => AttendanceRecord::where('event_id', $event->id)->whereNotNull('checked_in_at')->count(),
            'today_check_ins' => AttendanceRecord::where('event_id', $event->id)->whereNotNull('checked_in_at')->whereDate('checked_in_at', today())->count(),
        ];
    }

    private function emptyStats(): array
    {
        return ['total_registrations' => 0, 'this_week' => 0, 'total_check_ins' => 0, 'today_check_ins' => 0];
    }

    private function isLiveToday(Event $event): bool
    {
        if (! $event->starts_at) {
            return false;
        }

        return $event->starts_at->startOfDay()->lte(now())
            && (! $event->ends_at || $event->ends_at->endOfDay()->gte(now()));
    }

    private function recentRegistrations(Event $event): Collection
    {
        return Registration::where('event_id', $event->id)
            ->with('ticket')
            ->latest()
            ->limit(10)
            ->get();
    }

    private function recentRegistrationsPayload(Event $event): array
    {
        return $this->recentRegistrations($event)->map(fn ($r) => [
            'reference' => $r->reference_number,
            'name' => $r->full_name,
            'email' => $r->email,
            'ticket' => $r->ticket?->name,
            'status' => ucfirst($r->status),
            'time_ago' => $r->created_at->diffForHumans(),
        ])->all();
    }

    private function registrationCounts(array $eventIds): \Illuminate\Support\Collection
    {
        if (empty($eventIds)) {
            return collect();
        }

        return Registration::whereIn('event_id', $eventIds)
            ->whereIn('status', ['confirmed', 'registered'])
            ->selectRaw('event_id, COUNT(*) as count')
            ->groupBy('event_id')
            ->pluck('count', 'event_id');
    }
}
