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

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap">

@if ($cssEntry)
    <link rel="stylesheet" href="{{ asset('build/'.$cssEntry['file']) }}">
@endif

@if ($jsEntry)
    <script type="module" src="{{ asset('build/'.$jsEntry['file']) }}"></script>
@endif
