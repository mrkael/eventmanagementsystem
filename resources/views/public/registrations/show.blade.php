<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $form->title }}</title>
    @include('partials.assets')
</head>
<body class="bg-slate-100 text-slate-900">
    <main class="mx-auto grid min-h-screen max-w-6xl gap-6 px-4 py-6 lg:grid-cols-[.85fr_1.15fr] lg:py-10">
        <aside class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-6 lg:self-start">
            @if($event->banner_path)
                <img src="{{ asset('storage/'.$event->banner_path) }}" alt="{{ $event->title }} banner" class="mb-5 aspect-[16/9] w-full rounded-lg object-cover">
            @endif
            <p class="text-sm font-semibold text-emerald-700">{{ $event->starts_at->format('d M Y, H:i') }}</p>
            <h1 class="mt-2 text-2xl font-bold">{{ $event->title }}</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $event->summary }}</p>
            <dl class="mt-5 grid gap-3 text-sm">
                <div><dt class="font-medium text-slate-500">Venue</dt><dd>{{ $event->venue?->name ?? 'To be confirmed' }}</dd></div>
                <div><dt class="font-medium text-slate-500">Capacity</dt><dd>{{ number_format($event->capacity) }}</dd></div>
            </dl>
        </aside>

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            @if(session('status'))
                <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-semibold">Please review the highlighted issues.</p>
                    <ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <h2 class="text-xl font-semibold">{{ $form->title }}</h2>
            @if($form->description)<p class="mt-2 text-sm text-slate-600">{{ $form->description }}</p>@endif
            <form method="POST" action="{{ isset($invite) ? route('public.registrations.invite.store', $invite->token) : (request()->routeIs('public.registrations.private.show') ? route('public.registrations.private.store', $event->slug) : route('public.registrations.store', $event->slug)) }}" enctype="multipart/form-data" class="mt-6">
                @csrf
                @include('registrations._dynamic_form', ['form' => $form, 'invite' => $invite ?? null])
            </form>
        </section>
    </main>
</body>
</html>
