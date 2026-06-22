<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $renderedSubject }}</title>
</head>
<body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;padding:28px 16px;">
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;">
            <h1 style="margin:0 0 16px;font-size:22px;">{{ $registration->event->title }}</h1>
            <div style="font-size:15px;line-height:1.6;">{!! $renderedBody !!}</div>
            <div style="margin-top:24px;padding:18px;border:1px solid #d1fae5;background:#ecfdf5;border-radius:10px;text-align:center;">
                <p style="margin:0 0 12px;font-weight:bold;color:#047857;">E-ticket QR Code</p>
                <img src="{{ $ticketQr }}" alt="Registration QR code" style="width:180px;height:180px;">
                <p style="margin:12px 0 0;font-size:13px;color:#475569;">Reference: {{ $registration->reference_number }}</p>
            </div>
        </div>
    </div>
</body>
</html>
