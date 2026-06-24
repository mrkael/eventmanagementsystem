@props(['padding' => 'p-6'])

<section {{ $attributes->merge(['class' => "ds-card {$padding}"]) }}>
    {{ $slot }}
</section>
