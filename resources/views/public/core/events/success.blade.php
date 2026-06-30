<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration confirmed</title>
    @include('partials.assets')
</head>
<body class="bg-slate-50 text-slate-950">
    <main class="mx-auto max-w-2xl px-4 py-12">

        <div class="rounded-2xl border border-emerald-200 bg-white shadow-sm overflow-hidden">

            <div class="bg-emerald-50 px-6 py-8 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100">
                    <svg class="h-7 w-7 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold uppercase tracking-widest text-emerald-600">Registration Confirmed</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $registration->event->title }}</h1>
                @if($registration->event->starts_at)
                    <p class="mt-1 text-sm text-slate-500">{{ $registration->event->starts_at->format('d M Y, H:i') }}</p>
                @endif
            </div>

            <div class="px-6 py-6">
                <p class="mb-4 text-sm text-slate-600">
                    E-ticket(s) have been sent to each participant's email address.
                </p>

                <div class="overflow-hidden rounded-xl border border-slate-200">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Ticket</th>
                                <th class="px-4 py-3">Full Name</th>
                                <th class="px-4 py-3">Email</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($allRegistrations as $i => $reg)
                                <tr class="{{ $loop->even ? 'bg-slate-50/50' : 'bg-white' }}">
                                    <td class="px-4 py-3 text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-700">{{ $reg->ticket->name }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $reg->full_name }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $reg->email }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 rounded-xl bg-slate-50 border border-slate-100 px-5 py-4 text-sm text-slate-600 space-y-1.5">
                    <div><span class="font-semibold text-slate-700">Event:</span> {{ $registration->event->title }}</div>
                    @if($registration->event->location)
                        <div><span class="font-semibold text-slate-700">Venue:</span> {{ $registration->event->location }}</div>
                    @endif
                    <div><span class="font-semibold text-slate-700">Ticket:</span> {{ $registration->ticket->name }}</div>
                    <div><span class="font-semibold text-slate-700">Reference:</span> {{ $registration->reference_number }}</div>
                </div>
            </div>

        </div>

    </main>
</body>
</html>
