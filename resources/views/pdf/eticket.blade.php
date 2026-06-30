<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>E-Ticket – {{ $registration->reference_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1e293b; background: #ffffff; }

        .page { padding: 32px 36px; }

        .header { background: #1e293b; border-radius: 8px 8px 0 0; padding: 22px 28px; }
        .header-title { font-size: 9px; font-weight: bold; letter-spacing: 2px; color: #94a3b8; text-transform: uppercase; }
        .header-event { font-size: 20px; font-weight: bold; color: #ffffff; margin-top: 4px; line-height: 1.3; }

        .body-card { border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 8px 8px; padding: 0; }

        .main-row { width: 100%; border-collapse: collapse; }
        .details-cell { width: 62%; padding: 24px 28px 24px 28px; vertical-align: top; border-right: 1px solid #e2e8f0; }
        .qr-cell { width: 38%; padding: 24px 28px; vertical-align: middle; text-align: center; }

        .section-label { font-size: 8px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; color: #94a3b8; margin-bottom: 10px; }

        .participant-name { font-size: 17px; font-weight: bold; color: #0f172a; }
        .participant-email { font-size: 11px; color: #64748b; margin-top: 2px; }

        .divider { border: none; border-top: 1px solid #f1f5f9; margin: 16px 0; }

        .detail-grid { width: 100%; border-collapse: collapse; }
        .detail-row td { padding: 5px 0; vertical-align: top; }
        .detail-key { font-size: 9px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; color: #94a3b8; width: 40%; }
        .detail-val { font-size: 11px; color: #1e293b; font-weight: 600; }

        .qr-label { font-size: 8px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; color: #94a3b8; margin-bottom: 10px; }
        .qr-img { width: 150px; height: 150px; }

        .ref-badge { margin-top: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; display: inline-block; }
        .ref-label { font-size: 8px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; color: #94a3b8; }
        .ref-value { font-size: 11px; font-weight: bold; color: #0f172a; margin-top: 2px; letter-spacing: 0.5px; }

        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #94a3b8; }

        .ticket-badge { display: inline-block; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 3px 10px; font-size: 9px; font-weight: bold; color: #1d4ed8; letter-spacing: 0.5px; margin-top: 10px; }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div class="header-title">E-Ticket</div>
        <div class="header-event">{{ $registration->event->title }}</div>
    </div>

    <div class="body-card">
        <table class="main-row">
            <tr>
                <td class="details-cell">
                    <div class="section-label">Participant</div>
                    <div class="participant-name">{{ $registration->full_name }}</div>
                    <div class="participant-email">{{ $registration->email }}</div>
                    <div class="ticket-badge">{{ $registration->ticket->name }}</div>

                    <hr class="divider">

                    <div class="section-label">Event Details</div>
                    <table class="detail-grid">
                        @if($registration->event->starts_at)
                        <tr class="detail-row">
                            <td class="detail-key">Date &amp; Time</td>
                            <td class="detail-val">{{ $registration->event->starts_at->format('d M Y, H:i') }}</td>
                        </tr>
                        @endif
                        @if($registration->event->location)
                        <tr class="detail-row">
                            <td class="detail-key">Venue</td>
                            <td class="detail-val">{{ $registration->event->location }}</td>
                        </tr>
                        @endif
                        <tr class="detail-row">
                            <td class="detail-key">Ticket</td>
                            <td class="detail-val">{{ $registration->ticket->name }}</td>
                        </tr>
                        <tr class="detail-row">
                            <td class="detail-key">Reference</td>
                            <td class="detail-val">{{ $registration->reference_number }}</td>
                        </tr>
                    </table>
                </td>
                <td class="qr-cell">
                    <div class="qr-label">Scan to Check In</div>
                    <img src="{{ $qrPng }}" class="qr-img" alt="QR Code">
                    <div class="ref-badge">
                        <div class="ref-label">Reference No.</div>
                        <div class="ref-value">{{ $registration->reference_number }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Please present this e-ticket at the event entrance for check-in.
    </div>

</div>
</body>
</html>
