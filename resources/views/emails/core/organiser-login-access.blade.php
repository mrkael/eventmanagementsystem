<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} organiser login access</title>
</head>
<body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;padding:28px 16px;">
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;">
            <h1 style="margin:0 0 16px;font-size:22px;line-height:1.3;">Welcome, {{ $organiser->name }}</h1>
            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">Your organiser access for {{ config('app.name') }} is ready.</p>
            <div style="margin:20px 0;padding:16px;border:1px solid #dbeafe;background:#eff6ff;border-radius:10px;font-size:14px;line-height:1.7;">
                <p style="margin:0;"><strong>Login URL:</strong> <a href="{{ route('login') }}">{{ route('login') }}</a></p>
                <p style="margin:8px 0 0;"><strong>Email:</strong> {{ $user->email }}</p>
                @if($temporaryPassword)
                    <p style="margin:8px 0 0;"><strong>Temporary password:</strong> {{ $temporaryPassword }}</p>
                @else
                    <p style="margin:8px 0 0;">Use your existing account password to sign in.</p>
                @endif
            </div>
            <p style="margin:0;font-size:13px;line-height:1.6;color:#64748b;">For security, change your password after signing in if a temporary password was issued.</p>
        </div>
    </div>
</body>
</html>
