<?php

namespace App\Services\Core;

use App\Models\AttendanceRecord;
use App\Models\AttendanceScanLog;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CoreAttendanceService
{
    public function scan(Event $event, EventSession $session, string $rawToken, string $action, User $user, array $meta = []): Registration
    {
        abort_unless($session->event_id === $event->id, 404);
        $hash = hash('sha256', trim($rawToken));
        $error = null;

        $registration = DB::transaction(function () use ($event, $session, $hash, $rawToken, $action, $user, $meta, &$error) {
            $registration = Registration::where('qr_token_hash', $hash)->lockForUpdate()->first();

            if (! $registration) {
                $this->log($event, $session, null, $user, $action, 'failed', 'QR code not found.', $hash, $rawToken, $meta);
                $error = 'QR code not found.';

                return null;
            }

            if (! in_array($registration->status, ['confirmed', 'registered'], true)) {
                $this->log($event, $session, $registration, $user, $action, 'failed', 'Registration status is not valid for check-in.', $hash, $rawToken, $meta);
                $error = 'Registration status is not valid for check-in.';

                return null;
            }

            if ($registration->event_id !== $event->id) {
                $this->log($event, $session, $registration, $user, $action, 'failed', 'Participant belongs to another event.', $hash, $rawToken, $meta);
                $error = 'Participant belongs to another event.';

                return null;
            }

            if (! $session->tickets()->whereKey($registration->ticket_id)->exists()) {
                $this->log($event, $session, $registration, $user, $action, 'failed', 'Ticket is not allowed for this session.', $hash, $rawToken, $meta);
                $error = 'Ticket is not allowed for this session.';

                return null;
            }

            $record = AttendanceRecord::firstOrCreate([
                'event_id' => $event->id,
                'event_session_id' => $session->id,
                'registration_id' => $registration->id,
            ], [
                'ticket_id' => $registration->ticket_id,
                'status' => 'checked_in',
            ]);

            if ($action === 'check_out') {
                if (! $session->checkout_enabled) {
                    $error = 'Check-out is disabled for this session.';
                } elseif (! $record->checked_in_at) {
                    $error = 'Participant must check in before check-out.';
                } elseif ($record->checked_out_at) {
                    $error = 'Participant already checked out.';
                } else {
                    $record->update(['checked_out_at' => now(), 'checked_out_by' => $user->id]);
                }
            } else {
                if ($session->one_time_check_in && $record->checked_in_at) {
                    $error = 'Participant already checked in.';
                } else {
                    $record->update([
                        'ticket_id' => $registration->ticket_id,
                        'checked_in_at' => $record->checked_in_at ?: now(),
                        'checked_in_by' => $user->id,
                        'status' => 'checked_in',
                    ]);
                }
            }

            if ($error) {
                $this->log($event, $session, $registration, $user, $action, 'failed', $error, $hash, $rawToken, $meta);

                return null;
            }

            $this->log($event, $session, $registration, $user, $action, 'success', 'Scan accepted.', $hash, $rawToken, $meta);

            return $registration->fresh(['ticket', 'answers']);
        });

        if ($error) {
            throw ValidationException::withMessages(['token' => $error]);
        }

        return $registration;
    }

    public function counter(EventSession $session): array
    {
        $ticketIds = $session->tickets()->pluck('tickets.id');
        $eligible = $ticketIds->isEmpty()
            ? 0
            : Registration::where('event_id', $session->event_id)
                ->whereIn('ticket_id', $ticketIds)
                ->whereIn('status', ['confirmed', 'registered'])
                ->count();
        $checkedIn = $session->attendanceRecords()->whereNotNull('checked_in_at')->count();
        $checkedOut = $session->attendanceRecords()->whereNotNull('checked_out_at')->count();

        return [
            'eligible' => $eligible,
            'checked_in' => $checkedIn,
            'pending' => max(0, $eligible - $checkedIn),
            'checked_out' => $checkedOut,
            'percentage' => $eligible > 0 ? round(($checkedIn / $eligible) * 100, 1) : 0,
        ];
    }

    public function checkedInRecords(EventSession $session): Collection
    {
        return $session->attendanceRecords()
            ->with('registration.ticket', 'checkedInBy')
            ->whereNotNull('checked_in_at')
            ->latest('checked_in_at')
            ->get();
    }

    private function log(Event $event, EventSession $session, ?Registration $registration, User $user, string $action, string $result, string $message, string $hash, string $rawToken, array $meta): AttendanceScanLog
    {
        return AttendanceScanLog::create([
            'event_id' => $event->id,
            'event_session_id' => $session->id,
            'registration_id' => $registration?->id,
            'ticket_id' => $registration?->ticket_id,
            'scanned_by' => $user->id,
            'action' => $action,
            'result' => $result,
            'scan_result' => $result,
            'message' => $message,
            'qr_token' => $rawToken,
            'scan_token_hash' => $hash,
            'device_name' => $meta['device_name'] ?? null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'scanned_at' => now(),
        ]);
    }
}
