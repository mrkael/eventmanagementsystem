<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventReport;
use App\Models\Registration;
use App\Models\Ticket;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public const COLUMNS = [
        'tickets' => ['name', 'description', 'currency', 'price', 'quantity', 'available_quantity', 'sales_start_at', 'sales_end_at', 'status'],
        'attendees' => ['reference_number', 'full_name', 'email', 'phone', 'organization', 'designation', 'status', 'payment_status', 'ticket_price', 'final_amount', 'created_at'],
    ];

    public function index(Event $event): View
    {
        return view('admin.core.reports.index', [
            'event' => $event,
            'reports' => $event->reports()->latest()->get(),
            'columns' => self::COLUMNS,
            'overview' => [
                'ticket_amount_purchased' => $event->tickets()->sum('quantity') - $event->tickets()->sum('available_quantity'),
                'total_people_registered' => $event->coreRegistrations()->count(),
                'total_email_sent' => \App\Models\EmailCampaign::where('event_id', $event->id)->where('status', 'sent')->sum('recipient_count'),
                'total_rsvp' => $event->coreRegistrations()->where('status', 'confirmed')->count(),
            ],
        ]);
    }

    public function store(Request $request, Event $event, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'module' => ['required', 'in:tickets,attendees'],
            'selected_columns' => ['required', 'array', 'min:1'],
            'show_on_overview' => ['nullable', 'boolean'],
        ]);
        $report = $event->reports()->create($data + ['created_by' => $request->user()->id, 'show_on_overview' => $request->boolean('show_on_overview')]);
        $auditLogger->record('eevee.reports.create', "Created report {$report->name}.", $report);

        return back()->with('status', 'Custom report saved.');
    }

    public function export(Event $event, EventReport $report)
    {
        abort_unless($report->event_id === $event->id, 404);
        $rows = $report->module === 'tickets'
            ? Ticket::where('event_id', $event->id)->get()
            : Registration::where('event_id', $event->id)->get();
        $columns = $report->selected_columns;
        $csv = implode(',', $columns)."\n";
        foreach ($rows as $row) {
            $csv .= collect($columns)->map(fn ($column) => '"'.str_replace('"', '""', (string) data_get($row, $column)).'"')->implode(',')."\n";
        }

        return Response::make($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="'.$report->name.'.csv"']);
    }
}
