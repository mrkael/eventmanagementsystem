<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $event->title }}</title>@include('partials.assets')</head>
<body class="bg-white text-slate-950">
    <main>
        @php($sections = $event->publishedPage?->sections ?? collect())
        @forelse($sections as $section)
            <section class="border-b border-slate-100 px-4 py-12">
                <div class="mx-auto max-w-6xl">
                    @if($section->type === 'hero')
                        <div class="grid gap-8 lg:grid-cols-[1.1fr_.9fr] lg:items-center">
                            <div>
                                @if($event->logo_path)<img src="{{ asset('storage/'.$event->logo_path) }}" alt="{{ $event->title }} logo" class="mb-5 h-14 w-auto">@endif
                                <h1 class="text-4xl font-bold tracking-normal sm:text-5xl">{{ $section->title ?: $event->title }}</h1>
                                <p class="mt-5 max-w-2xl text-lg text-slate-600">{{ $section->content ?: $event->summary }}</p>
                                <p class="mt-5 text-sm font-semibold" style="color: {{ $event->brand_color ?? '#047857' }}">{{ $event->starts_at->format('d M Y, H:i') }} · {{ $event->location }}</p>
                            </div>
                            @if($event->banner_path)<img src="{{ asset('storage/'.$event->banner_path) }}" alt="{{ $event->title }} banner" class="aspect-[4/3] w-full rounded-lg object-cover">@endif
                        </div>
                    @elseif($section->type === 'registration_cta')
                        <div class="rounded-lg p-8 text-white" style="background: {{ $event->brand_color ?? '#047857' }}">
                            <h2 class="text-2xl font-bold">{{ $section->title ?: 'Register now' }}</h2>
                            <p class="mt-3">{{ $section->content }}</p>
                            <div class="mt-6 grid gap-3 md:grid-cols-3">
                                @forelse($tickets as $ticket)
                                    <a href="{{ route('core.public.register', [$event, $ticket]) }}" class="rounded-lg bg-white p-4 text-slate-950">
                                        <span class="block font-semibold">{{ $ticket->name }}</span>
                                        <span class="mt-1 block text-sm text-slate-600">{{ $ticket->available_quantity }} available</span>
                                        <span class="mt-3 block font-bold">{{ $ticket->price > 0 ? $ticket->currency.' '.number_format($ticket->price, 2) : 'Free' }}</span>
                                    </a>
                                @empty
                                    <p>No tickets are currently available.</p>
                                @endforelse
                            </div>
                        </div>
                    @else
                        <h2 class="text-2xl font-bold">{{ $section->title }}</h2>
                        <p class="mt-4 whitespace-pre-line text-slate-600">{{ $section->content }}</p>
                    @endif
                </div>
            </section>
        @empty
            <section class="px-4 py-16"><div class="mx-auto max-w-5xl"><h1 class="text-4xl font-bold">{{ $event->title }}</h1><p class="mt-4 text-slate-600">{{ $event->summary }}</p></div></section>
        @endforelse
    </main>
</body>
</html>
