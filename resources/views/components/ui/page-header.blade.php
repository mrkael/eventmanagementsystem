@props(['eyebrow' => null, 'title', 'description' => null])

<div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
    <div class="max-w-3xl">
        @if($eyebrow)
            <p class="text-xs font-bold uppercase text-blue-600">{{ $eyebrow }}</p>
        @endif
        <h1 class="mt-2 text-3xl font-semibold tracking-normal text-slate-950 sm:text-4xl">{{ $title }}</h1>
        @if($description)
            <p class="mt-3 text-base leading-7 text-slate-600">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap gap-3">{{ $actions }}</div>
    @endisset
</div>
