<?php

namespace App\Services\Attendance;

use App\Enums\ParticipantRegistrationStatus;
use App\Models\AttendanceLog;
use App\Models\AttendanceQrToken;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\ParticipantRegistration;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function generateQr(ParticipantRegistration $registration, ?User $user = null): array
    {
        if (! in_array($registration->status, [ParticipantRegistrationStatus::Confirmed, ParticipantRegistrationStatus::Attended], true)) {
            throw ValidationException::withMessages(['registration' => 'Only confirmed participants can receive attendance QR codes.']);
        }

        $rawToken = 'EMS-'.$registration->event_id.'-'.Str::upper(Str::random(32));
        $token = AttendanceQrToken::create([
            'event_id' => $registration->event_id,
            'participant_registration_id' => $registration->id,
            'generated_by' => $user?->id,
            'token_hash' => $this->hash($rawToken),
            'expires_at' => $this->expiryFor($registration->event),
        ]);

        $this->log($registration->event, $registration, $token, 'qr_generated', 'success', null, 'Attendance QR generated.');

        return ['token' => $rawToken, 'record' => $token];
    }

    public function checkIn(Event $event, string $rawToken, User $user, array $meta = []): ParticipantRegistration
    {
        return $this->scan($event, $rawToken, $user, 'check_in', $meta);
    }

    public function checkOut(Event $event, string $rawToken, User $user, array $meta = []): ParticipantRegistration
    {
        return $this->scan($event, $rawToken, $user, 'check_out', $meta);
    }

    public function manualOverride(Event $event, ParticipantRegistration $registration, User $user, string $action, string $reason, ?string $notes = null): ParticipantRegistration
    {
        abort_unless($registration->event_id === $event->id, 404);

        return DB::transaction(function () use ($event, $registration, $user, $action, $reason, $notes) {
            $registration = ParticipantRegistration::whereKey($registration->id)->lockForUpdate()->firstOrFail();

            if ($action === 'check_in') {
                $registration->update([
                    'status' => ParticipantRegistrationStatus::Attended,
                    'checked_in_at' => $registration->checked_in_at ?: now(),
                    'attendance_notes' => $notes,
                ]);
            } elseif ($action === 'check_out') {
                $registration->update([
                    'checked_out_at' => $registration->checked_out_at ?: now(),
                    'attendance_notes' => $notes,
                ]);
            } elseif ($action === 'no_show') {
                $registration->update([
                    'status' => ParticipantRegistrationStatus::NoShow,
                    'attendance_notes' => $notes,
                ]);
            }

            $this->log($event, $registration, null, 'manual_override_'.$action, 'success', null, $notes, $reason, $user);

            return $registration->fresh();
        });
    }

    public function dashboard(Event $event): array
    {
        $totalRegistered = $event->participantRegistrations()->count();
        $checkedIn = $event->participantRegistrations()->whereNotNull('checked_in_at')->count();
        $checkedOut = $event->participantRegistrations()->whereNotNull('checked_out_at')->count();
        $noShows = $event->participantRegistrations()->where('status', ParticipantRegistrationStatus::NoShow->value)->count();

        return [
            'total_registered' => $totalRegistered,
            'total_checked_in' => $checkedIn,
            'total_checked_out' => $checkedOut,
            'attendance_rate' => $totalRegistered > 0 ? round(($checkedIn / $totalRegistered) * 100, 1) : 0,
            'no_show_rate' => $totalRegistered > 0 ? round(($noShows / $totalRegistered) * 100, 1) : 0,
        ];
    }

    public function exportExcel(Event $event): Response
    {
        $rows = $this->exportRows($event);
        $html = view('admin.attendance.exports.excel', compact('event', 'rows'))->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="attendance-'.$event->id.'.xls"',
        ]);
    }

    public function exportPdf(Event $event): Response
    {
        $rows = $this->exportRows($event);
        $lines = collect(["Attendance Report: {$event->title}", 'Generated: '.now()->format('Y-m-d H:i'), ''])
            ->merge($rows->map(fn ($row) => implode(' | ', $row)))
            ->all();
        $pdf = $this->simplePdf($lines);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="attendance-'.$event->id.'.pdf"',
        ]);
    }

    private function scan(Event $event, string $rawToken, User $user, string $action, array $meta): ParticipantRegistration
    {
        $hash = $this->hash($rawToken);
        $error = null;
        $sessionId = $this->validSessionId($event, $meta['event_session_id'] ?? null);

        $registration = DB::transaction(function () use ($event, $hash, $user, $action, $meta, $sessionId, &$error) {
            $token = AttendanceQrToken::with('registration')->where('token_hash', $hash)->lockForUpdate()->first();

            if (! $token) {
                $this->log($event, null, null, $action, 'failed', $hash, 'Invalid QR token.', null, $user, $meta, $sessionId);
                $error = 'Invalid QR code.';

                return null;
            }

            if ($token->event_id !== $event->id) {
                $this->log($event, $token->registration, $token, $action, 'failed', $hash, 'Cross-event QR usage blocked.', null, $user, $meta, $sessionId);
                $error = 'This QR code belongs to another event.';

                return null;
            }

            if ($token->revoked_at || ($token->expires_at && now()->gt($token->expires_at))) {
                $this->log($event, $token->registration, $token, $action, 'failed', $hash, 'Expired or revoked QR token.', null, $user, $meta, $sessionId);
                $error = 'This QR code is expired or revoked.';

                return null;
            }

            $registration = ParticipantRegistration::whereKey($token->participant_registration_id)->lockForUpdate()->firstOrFail();

            if ($action === 'check_in') {
                if ($registration->checked_in_at) {
                    $this->log($event, $registration, $token, $action, 'failed', $hash, 'Duplicate check-in prevented.', null, $user, $meta, $sessionId);
                    $error = 'Participant is already checked in.';

                    return null;
                }

                if ($registration->status !== ParticipantRegistrationStatus::Confirmed) {
                    $this->log($event, $registration, $token, $action, 'failed', $hash, 'Participant is not confirmed.', null, $user, $meta, $sessionId);
                    $error = 'Only confirmed participants can be checked in.';

                    return null;
                }

                $registration->update(['status' => ParticipantRegistrationStatus::Attended, 'checked_in_at' => now()]);
            }

            if ($action === 'check_out') {
                if (! $registration->checked_in_at) {
                    $this->log($event, $registration, $token, $action, 'failed', $hash, 'Checkout before check-in prevented.', null, $user, $meta, $sessionId);
                    $error = 'Participant must be checked in before checkout.';

                    return null;
                }

                if ($registration->checked_out_at) {
                    $this->log($event, $registration, $token, $action, 'failed', $hash, 'Duplicate checkout prevented.', null, $user, $meta, $sessionId);
                    $error = 'Participant is already checked out.';

                    return null;
                }

                $registration->update(['checked_out_at' => now()]);
            }

            $token->update(['last_used_at' => now()]);
            $this->log($event, $registration, $token, $action, 'success', $hash, $meta['notes'] ?? null, null, $user, $meta, $sessionId);

            return $registration->fresh();
        });

        if ($error) {
            throw ValidationException::withMessages(['token' => $error]);
        }

        return $registration;
    }

    private function exportRows(Event $event)
    {
        return $event->participantRegistrations()
            ->orderBy('name')
            ->get()
            ->map(fn (ParticipantRegistration $registration) => [
                'Name' => $registration->name,
                'Email' => $registration->email,
                'Status' => $registration->status->label(),
                'Checked In' => $registration->checked_in_at?->format('Y-m-d H:i') ?? '',
                'Checked Out' => $registration->checked_out_at?->format('Y-m-d H:i') ?? '',
                'Notes' => $registration->attendance_notes ?? '',
            ]);
    }

    private function expiryFor(Event $event): ?\Carbon\CarbonInterface
    {
        $hours = $event->configuration?->qr_rules['expires_after_event_hours'] ?? 24;

        return $event->ends_at?->copy()->addHours((int) $hours);
    }

    private function hash(string $rawToken): string
    {
        return hash('sha256', trim($rawToken));
    }

    private function validSessionId(Event $event, mixed $sessionId): ?int
    {
        if (blank($sessionId)) {
            return null;
        }

        return EventSession::whereKey($sessionId)->where('event_id', $event->id)->value('id');
    }

    private function log(Event $event, ?ParticipantRegistration $registration, ?AttendanceQrToken $token, string $action, string $result, ?string $hash = null, ?string $notes = null, ?string $reason = null, ?User $user = null, array $meta = [], ?int $sessionId = null): AttendanceLog
    {
        return AttendanceLog::create([
            'event_id' => $event->id,
            'event_session_id' => $sessionId,
            'participant_registration_id' => $registration?->id,
            'attendance_qr_token_id' => $token?->id,
            'scanned_by' => $user?->id,
            'action' => $action,
            'result' => $result,
            'scan_token_hash' => $hash,
            'device_name' => $meta['device_name'] ?? null,
            'reason' => $reason,
            'notes' => $notes,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'latitude' => isset($meta['latitude']) ? (float) $meta['latitude'] : null,
            'longitude' => isset($meta['longitude']) ? (float) $meta['longitude'] : null,
            'location_name' => $meta['location_name'] ?? null,
        ]);
    }

    private function simplePdf(array $lines): string
    {
        $content = 'BT /F1 10 Tf 40 780 Td ';
        foreach ($lines as $index => $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], Str::limit($line, 130, ''));
            $content .= ($index ? '0 -14 Td ' : '')."({$escaped}) Tj ";
        }
        $content .= 'ET';
        $objects = [
            '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj',
            '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj',
            '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj',
            '4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj',
            '5 0 obj << /Length '.strlen($content)." >> stream\n{$content}\nendstream endobj",
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object."\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= 'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }
}
