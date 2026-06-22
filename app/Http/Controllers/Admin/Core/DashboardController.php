<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\Registration;
use App\Models\Ticket;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $totalRegistrations = Registration::count();
        $checkedIn = AttendanceRecord::whereNotNull('checked_in_at')->count();

        return view('admin.core.dashboard', [
            'totalEvents' => Event::count(),
            'activeEvents' => Event::where('status_key', 'published')->count(),
            'totalRegistrations' => $totalRegistrations,
            'attendancePercentage' => $totalRegistrations > 0 ? round(($checkedIn / $totalRegistrations) * 100, 1) : 0,
            'ticketCounts' => Ticket::withCount('registrations')->latest()->limit(8)->get(),
            'sessionCounts' => EventSession::withCount(['attendanceRecords as checked_in_count' => fn ($query) => $query->whereNotNull('checked_in_at')])->latest()->limit(8)->get(),
            'recentRegistrations' => Registration::with('event', 'ticket')->latest()->limit(10)->get(),
        ]);
    }
}
