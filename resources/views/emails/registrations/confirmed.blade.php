<p>Hello {{ $registration->name }},</p>
<p>Your registration for <strong>{{ $registration->event->title }}</strong> is confirmed.</p>
<p>Date: {{ $registration->event->starts_at->format('d M Y, H:i') }}</p>
@if($ticketToken && $ticketQr)
    <div style="margin: 20px 0; padding: 16px; border: 1px solid #d1d5db; border-radius: 8px;">
        <p style="font-weight: 700; margin: 0 0 12px;">E-ticket QR Code</p>
        <img src="{{ $ticketQr }}" alt="Attendance QR code" width="220" height="220" style="display: block; border: 1px solid #e5e7eb; padding: 8px;">
        <p style="font-family: monospace; font-size: 12px; word-break: break-all;">{{ $ticketToken }}</p>
        <p style="font-size: 12px; color: #4b5563;">Show this QR code at the event check-in counter.</p>
    </div>
@endif
<p>Thank you.</p>
