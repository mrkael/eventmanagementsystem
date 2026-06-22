<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Registration confirmed</title>@include('partials.assets')</head>
<body class="bg-slate-50 text-slate-950">
    <main class="mx-auto max-w-2xl px-4 py-12">
        <div class="rounded-lg border border-emerald-200 bg-white p-6 text-center shadow-sm">
            <p class="text-sm font-semibold text-emerald-700">Registration confirmed</p>
            <h1 class="mt-2 text-3xl font-bold">{{ $registration->full_name }}</h1>
            <p class="mt-3 text-slate-600">Your e-ticket has been sent to {{ $registration->email }}.</p>
            <div class="mt-6 rounded-lg bg-slate-50 p-4 text-left text-sm">
                <p><strong>Reference:</strong> {{ $registration->reference_number }}</p>
                <p><strong>Event:</strong> {{ $registration->event->title }}</p>
                <p><strong>Ticket:</strong> {{ $registration->ticket->name }}</p>
            </div>
        </div>
    </main>
</body>
</html>
