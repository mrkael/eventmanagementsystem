@props(['icon' => 'spark', 'title', 'description'])

<div {{ $attributes->merge(['class' => 'rounded-[24px] border border-dashed border-slate-300 bg-white/72 p-10 text-center']) }}>
    <div class="mx-auto grid size-12 place-items-center rounded-2xl bg-blue-50 text-blue-700">
        <x-ui.icon :name="$icon" class="size-6" />
    </div>
    <h3 class="mt-5 text-lg font-semibold text-slate-950">{{ $title }}</h3>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">{{ $description }}</p>
</div>
