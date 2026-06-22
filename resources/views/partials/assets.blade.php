@php
    $manifestPath = public_path('build/manifest.json');
    $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];

    $assetEntry = function (string $source) use ($manifest): ?array {
        foreach ($manifest as $key => $entry) {
            $normalizedKey = str_replace('\\', '/', $key);

            if ($normalizedKey === $source || str_ends_with($normalizedKey, $source)) {
                return $entry;
            }
        }

        return null;
    };

    $cssEntry = $assetEntry('resources/css/app.css');
    $jsEntry = $assetEntry('resources/js/app.js');
@endphp

@if ($cssEntry)
    <link rel="stylesheet" href="{{ asset('build/'.$cssEntry['file']) }}">
@endif

@if ($jsEntry)
    <script type="module" src="{{ asset('build/'.$jsEntry['file']) }}"></script>
@endif
