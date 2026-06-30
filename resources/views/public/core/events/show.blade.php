<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $event->title }}</title>
    @include('partials.assets')
</head>
<body class="bg-white text-slate-950">
    @php
        $sections = $sections ?? $event->publishedPage?->sections ?? collect();
        $oldSelections = old('ticket_selections', []);
        $hasOldSubmission = !empty($oldSelections);
        $multiSubmitUrl = route('core.public.submit.multi', ['event' => $event->custom_url]);
        $safeCssProps = [
            'text-align' => '/^(left|center|right|justify)$/i',
            'float' => '/^(left|right|none)$/i',
            'display' => '/^(block|table|table-cell|table-row|inline-block)$/i',
            'border-collapse' => '/^(collapse|separate)$/i',
            'border-spacing' => '/^\d+(\.\d+)?(px|em|rem)(\s+\d+(\.\d+)?(px|em|rem))?$/i',
            'width' => '/^(auto|\d+(\.\d+)?(px|em|rem|%))$/i',
            'height' => '/^(auto|\d+(\.\d+)?(px|em|rem|%))$/i',
            'min-width' => '/^(auto|\d+(\.\d+)?(px|em|rem|%))$/i',
            'max-width' => '/^(auto|none|\d+(\.\d+)?(px|em|rem|%))$/i',
            'padding' => '/^(\d+(\.\d+)?(px|em|rem|%)\s*){1,4}$/i',
            'padding-top' => '/^\d+(\.\d+)?(px|em|rem|%)$/i',
            'padding-right' => '/^\d+(\.\d+)?(px|em|rem|%)$/i',
            'padding-bottom' => '/^\d+(\.\d+)?(px|em|rem|%)$/i',
            'padding-left' => '/^\d+(\.\d+)?(px|em|rem|%)$/i',
            'margin-left' => '/^(auto|\d+(\.\d+)?(px|em|rem|%))$/i',
            'margin-right' => '/^(auto|\d+(\.\d+)?(px|em|rem|%))$/i',
            'margin-top' => '/^\d+(\.\d+)?(px|em|rem|%)$/i',
            'margin-bottom' => '/^\d+(\.\d+)?(px|em|rem|%)$/i',
            'background-color' => '/^(#[0-9a-f]{3,8}|rgb\(\d{1,3},\s*\d{1,3},\s*\d{1,3}\)|rgba\(\d{1,3},\s*\d{1,3},\s*\d{1,3},\s*[\d.]+\)|[a-z]+)$/i',
            'color' => '/^(#[0-9a-f]{3,8}|rgb\(\d{1,3},\s*\d{1,3},\s*\d{1,3}\)|rgba\(\d{1,3},\s*\d{1,3},\s*\d{1,3},\s*[\d.]+\)|[a-z]+)$/i',
            'font-weight' => '/^(normal|bold|bolder|lighter|\d{3})$/i',
            'font-size' => '/^\d+(\.\d+)?(px|em|rem|pt|%)$/i',
            'vertical-align' => '/^(top|middle|bottom|baseline)$/i',
        ];
        $siteHtml = function (?string $content) use ($safeCssProps): string {
            $content = preg_replace('#<(script|style|iframe|object|embed)[^>]*>.*?</\1>#is', '', (string) $content) ?? '';
            $content = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $content) ?? '';
            $content = preg_replace('/(href|src)\s*=\s*([\'"])\s*javascript:[^\'"]*\2/i', '$1="#"', $content) ?? '';
            $content = preg_replace_callback('/\sstyle\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', function (array $matches) use ($safeCssProps) {
                $style = trim($matches[1], '"\'');
                $safe = [];
                foreach (array_filter(array_map('trim', explode(';', $style))) as $part) {
                    $colon = strpos($part, ':');
                    if ($colon === false) continue;
                    $prop = strtolower(trim(substr($part, 0, $colon)));
                    $val = trim(substr($part, $colon + 1));
                    if (isset($safeCssProps[$prop]) && preg_match($safeCssProps[$prop], $val) && !preg_match('/javascript:|expression\s*\(/i', $val)) {
                        $safe[] = $prop.': '.$val;
                    }
                }
                return $safe ? ' style="'.e(implode('; ', $safe).';').'"' : '';
            }, $content) ?? '';
            $content = preg_replace('/\s(?!href\s*=|src\s*=|alt\s*=|title\s*=|target\s*=|rel\s*=|style\s*=|class\s*=|colspan\s*=|rowspan\s*=|scope\s*=|border\s*=|cellpadding\s*=|cellspacing\s*=|width\s*=|height\s*=)[a-z0-9:_-]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $content) ?? '';
            return trim(strip_tags($content, '<p><br><strong><b><em><i><u><h1><h2><h3><h4><ul><ol><li><a><img><div><span><blockquote><table><thead><tbody><tfoot><tr><th><td><caption><col><colgroup>'));
        };
        $ticketData = $tickets->map(fn ($ticket) => [
            'id' => $ticket->id,
            'name' => $ticket->name,
            'description' => $ticket->description,
            'quantity' => $ticket->quantity,
            'available_quantity' => $ticket->available_quantity,
            'min_quantity' => $ticket->min_quantity,
            'max_quantity' => $ticket->max_quantity,
            'submit_url' => route('core.public.submit', ['event' => $event->custom_url, 'ticket' => $ticket]),
            'form' => $ticket->form ? [
                'id' => $ticket->form->id,
                'title' => $ticket->form->title,
                'fields' => $ticket->form->fields->map(fn ($field) => [
                    'key' => $field->key,
                    'label' => $field->label,
                    'type' => $field->type,
                    'placeholder' => $field->placeholder,
                    'is_required' => $field->is_required,
                    'options' => $field->options ?: [],
                ])->values(),
            ] : null,
        ])->values();
    @endphp

    @if($isPreview ?? false)
        <div class="sticky top-0 z-50 border-b border-amber-200 bg-amber-50 px-4 py-3 text-center text-sm font-black text-amber-900">
            Site Preview - draft content, active tickets, and linked forms are shown for admin review.
        </div>
    @endif

    <main data-public-event data-tickets='@json($ticketData)' data-old-selections='@json($oldSelections)' data-multi-submit-url="{{ $multiSubmitUrl }}" data-referral="{{ $referral ?? '' }}">
        <div class="mx-auto max-w-5xl px-6 lg:px-10">
            @forelse($sections as $section)
                @if($section->type === 'registration_form')
                    @continue
                @endif

                @if(!$loop->first)
                    <hr class="border-slate-100">
                @endif

                @if($section->type === 'hero')
                    <section class="py-16 text-center">
                        @if(trim($section->title ?? ''))
                            <h1 class="text-5xl font-black leading-tight text-slate-950 md:text-6xl">{{ $section->title }}</h1>
                        @endif
                        @if($siteHtml($section->content))
                            <div class="mt-6 space-y-4 text-lg leading-8 text-slate-500 [&_a]:font-bold [&_a]:text-blue-700 [&_img]:mx-auto [&_img]:block [&_img]:h-auto [&_img]:max-w-full [&_img]:rounded-2xl [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6">{!! $siteHtml($section->content) !!}</div>
                        @endif
                    </section>

                @elseif($section->type === 'ticket_selection')
                    <section id="tickets" class="py-16">

                        {{-- Step 1: Ticket Selection --}}
                        <div data-ticket-step @if($hasOldSubmission) style="display:none" @endif>
                            @if(session('status'))
                                <div class="mb-5 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3.5 text-sm font-medium text-emerald-700">{{ session('status') }}</div>
                            @endif

                            <div class="space-y-4">
                                @forelse($tickets as $ticket)
                                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                        <div class="flex items-stretch">
                                            <div class="flex-1 p-6 sm:p-8">
                                                <h3 class="text-xl font-bold text-slate-900">{{ $ticket->name }}</h3>
                                                @if($ticket->description)
                                                    <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $ticket->description }}</p>
                                                @endif
                                                @if($ticket->sales_end_at)
                                                    <div class="mt-3 flex items-center gap-1.5 text-xs text-slate-400">
                                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                                        <span>Registration ends {{ $ticket->sales_end_at->format('d M Y') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="w-px bg-slate-100"></div>
                                            <div class="flex w-44 shrink-0 flex-col items-center justify-center gap-2 p-6">
                                                <span class="text-xs font-medium text-slate-400">Quantity</span>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" data-qty-dec="{{ $ticket->id }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-xl leading-none text-slate-400 transition hover:text-slate-700 active:bg-slate-50">−</button>
                                                    <input type="number" data-ticket-quantity="{{ $ticket->id }}"
                                                           value="0"
                                                           min="0"
                                                           max="{{ min($ticket->max_quantity, $ticket->available_quantity) }}"
                                                           class="ticket-qty-input h-10 w-14 rounded-lg border border-slate-200 bg-white text-center text-sm font-bold text-slate-900 outline-none focus:border-slate-400">
                                                    <button type="button" data-qty-inc="{{ $ticket->id }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-xl leading-none text-slate-400 transition hover:text-slate-700 active:bg-slate-50">+</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center shadow-sm">
                                        <p class="text-sm font-medium text-slate-500">No active tickets are currently available.</p>
                                        <p class="mt-1 text-xs text-slate-400">Please check back later.</p>
                                    </div>
                                @endforelse
                            </div>

                            <div id="ticket-register-wrap" class="mt-8 flex justify-center" style="display:none">
                                <button type="button" id="ticket-register-btn" class="btn-71">
                                    Register
                                </button>
                            </div>
                        </div>

                        {{-- Step 2: Registration Form --}}
                        <div data-form-step @if(!$hasOldSubmission) class="hidden" @endif>
                            @if($errors->any())
                                <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 px-4 py-3.5 text-sm text-red-700">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                                    <span class="font-medium">{{ $errors->first() }}</span>
                                </div>
                            @endif

                            <button type="button" data-back-to-tickets class="mb-8 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                Back to ticket selection
                            </button>

                            <div data-registration-form>
                                <p class="py-8 text-center text-sm text-slate-400">Loading registration form&hellip;</p>
                            </div>
                        </div>

                    </section>

                @elseif($section->type === 'footer')
                    <footer class="py-16 text-center">
                        @if(trim($section->title ?? ''))
                            <h2 class="text-2xl font-black text-slate-950">{{ $section->title }}</h2>
                        @endif
                        @if($siteHtml($section->content))
                            <div class="mt-3 space-y-3 text-slate-500 [&_a]:font-bold [&_a]:text-blue-700 [&_img]:mx-auto [&_img]:block [&_img]:h-auto [&_img]:max-w-full [&_img]:rounded-2xl [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6">{!! $siteHtml($section->content) !!}</div>
                        @endif
                    </footer>

                @elseif($section->type === 'image')
                    <section class="py-14">
                        @if($section->settings['image_url'] ?? null)
                            <img src="{{ $section->settings['image_url'] }}" alt="{{ $section->title }}" class="h-auto w-full rounded-2xl">
                        @endif
                        @if($siteHtml($section->content))
                            <div class="mt-5 space-y-3 leading-7 text-slate-600 [&_a]:font-bold [&_a]:text-blue-700 [&_img]:mx-auto [&_img]:block [&_img]:h-auto [&_img]:max-w-full [&_img]:rounded-2xl [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6">{!! $siteHtml($section->content) !!}</div>
                        @endif
                    </section>

                @elseif($section->type === 'button_cta')
                    <section class="py-16 text-center">
                        @if(trim($section->title ?? ''))
                            <h2 class="text-3xl font-black text-slate-950">{{ $section->title }}</h2>
                        @endif
                        @if($siteHtml($section->content))
                            <div class="mt-4 space-y-3 text-lg text-slate-500 [&_a]:font-bold [&_a]:text-blue-700 [&_img]:mx-auto [&_img]:block [&_img]:h-auto [&_img]:max-w-full [&_img]:rounded-2xl [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6">{!! $siteHtml($section->content) !!}</div>
                        @endif
                        <a href="{{ $section->settings['button_url'] ?? '#tickets' }}" class="mt-7 inline-flex rounded-full bg-slate-950 px-8 py-3.5 text-sm font-black text-white">{{ $section->settings['button_label'] ?? 'View Tickets' }}</a>
                    </section>

                @else
                    <section class="py-14">
                        @if(trim($section->title ?? ''))
                            <h2 class="text-3xl font-black text-slate-950">{{ $section->title }}</h2>
                        @endif
                        @if($siteHtml($section->content))
                            <div class="mt-5 space-y-4 text-base leading-8 text-slate-600 [&_a]:font-bold [&_a]:text-blue-700 [&_img]:mx-auto [&_img]:block [&_img]:h-auto [&_img]:max-w-full [&_img]:rounded-2xl [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6">{!! $siteHtml($section->content) !!}</div>
                        @endif
                    </section>
                @endif
            @empty
                <section class="py-24 text-center">
                    <h1 class="text-5xl font-black text-slate-950">{{ $event->title }}</h1>
                    <p class="mt-5 text-lg text-slate-500">{{ $event->description }}</p>
                </section>
            @endforelse
        </div>
    </main>

    <style>
        .ticket-qty-input::-webkit-inner-spin-button,
        .ticket-qty-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        .ticket-qty-input { -moz-appearance: textfield; }

        .btn-71, .btn-71 *, .btn-71 :after, .btn-71 :before, .btn-71:after, .btn-71:before { border: 0 solid; box-sizing: border-box; }
        .btn-71 { -webkit-tap-highlight-color: transparent; -webkit-appearance: button; background-color: #000; background-image: none; color: #fff; cursor: pointer; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji; font-size: 80%; line-height: 1.5; margin: 0; -webkit-mask-image: -webkit-radial-gradient(#000, #fff); padding: 0; }
        .btn-71:disabled { cursor: default; }
        .btn-71:-moz-focusring { outline: auto; }
        .btn-71 svg { display: block; vertical-align: middle; }
        .btn-71 [hidden] { display: none; }
        .btn-71 { border: 1px solid; border-radius: 999px; box-sizing: border-box; display: block; font-weight: 900; overflow: hidden; padding: 1rem 2rem; position: relative; text-transform: uppercase; }
        .btn-71:before { --opacity: 0.2; aspect-ratio: 1; background: #fff; border-radius: 50%; content: ""; left: 50%; opacity: var(--opacity); position: absolute; top: 50%; transform: translate(-50%, -50%) scale(0); width: 100%; z-index: -1; }
        .btn-71:hover:before { -webkit-animation: btn71enlarge 1s forwards; animation: btn71enlarge 1s forwards; }
        @-webkit-keyframes btn71enlarge { to { opacity: 0; transform: translate(-50%, -50%) scale(4); } }
        @keyframes btn71enlarge { to { opacity: 0; transform: translate(-50%, -50%) scale(4); } }
    </style>

    <script>
        (() => {
            const root = document.querySelector('[data-public-event]');
            if (!root) return;

            const tickets = JSON.parse(root.dataset.tickets || '[]');
            const referral = root.dataset.referral || '';
            const multiSubmitUrl = root.dataset.multiSubmitUrl || '';
            const formTarget = root.querySelector('[data-registration-form]');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
            const uploadIcon = '<svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4" /><path d="m7 9 5-5 5 5" /><path d="M5 20h14" /></svg>';
            const successIcon = '<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5" /></svg>';
            const loadingIcon = '<svg class="size-6 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M21 12a9 9 0 0 0-9-9v3a6 6 0 0 1 6 6z"></path></svg>';

            const fileUploadHtml = (field, ticketId, pIdx) => {
                const key = field.key || field.label.toLowerCase().replace(/[^a-z0-9]+/g, '_');
                const name = `ticket_selections[${ticketId}][${pIdx}][${key}]`;
                const required = field.is_required ? 'required' : '';
                const uploadId = `upload_${ticketId}_${pIdx}_${String(field.key || field.label).replace(/[^a-z0-9]+/gi, '_')}`;

                return `
                    <div data-file-upload class="block">
                        <span class="text-sm font-bold text-slate-700">${escapeHtml(field.label)} ${field.is_required ? '<span class="text-red-600">*</span>' : ''}</span>
                        <label data-upload-dropzone for="${escapeHtml(uploadId)}" class="mt-2 block cursor-pointer rounded-[24px] border border-dashed border-slate-300 bg-slate-50 p-6 text-center transition hover:border-blue-300 hover:bg-blue-50">
                            <input id="${escapeHtml(uploadId)}" type="file" name="${escapeHtml(name)}" ${required} data-file-input class="sr-only" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <div data-upload-default>
                                <div class="mx-auto grid size-12 place-items-center rounded-2xl bg-white text-slate-500 shadow-sm">${uploadIcon}</div>
                                <p class="mt-3 text-sm font-bold text-slate-700">Click to upload or drag and drop</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">${escapeHtml(field.placeholder || 'Browse/select file')}</p>
                                <p class="mt-3 text-xs font-semibold text-slate-400">PDF, JPG, PNG, DOC, DOCX up to 10MB</p>
                            </div>
                            <div data-upload-loading class="hidden">
                                <div class="mx-auto grid size-12 place-items-center rounded-2xl bg-white text-blue-700 shadow-sm">${loadingIcon}</div>
                                <p data-upload-loading-name class="mt-3 truncate text-sm font-bold text-slate-700"></p>
                                <p class="mt-1 text-xs font-semibold text-blue-700">Uploading file...</p>
                                <div class="mx-auto mt-4 h-2 max-w-xs overflow-hidden rounded-full bg-slate-200">
                                    <div data-upload-progress class="h-full w-0 rounded-full bg-blue-700 transition-all duration-300"></div>
                                </div>
                            </div>
                            <div data-upload-success class="hidden">
                                <div class="mx-auto grid size-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-700">${successIcon}</div>
                                <p data-upload-file-name class="mt-3 truncate text-sm font-bold text-slate-800"></p>
                                <p class="mt-1 text-xs font-semibold text-emerald-700">File ready to submit</p>
                            </div>
                        </label>
                        <div data-upload-actions class="mt-3 hidden items-center gap-2">
                            <button type="button" data-replace-file class="rounded-full border border-slate-200 px-4 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">Replace file</button>
                            <button type="button" data-remove-file class="rounded-full border border-red-200 px-4 py-2 text-xs font-black text-red-700 hover:bg-red-50">Remove file</button>
                        </div>
                        ${field.error_text ? `<span class="mt-2 block text-xs font-semibold text-slate-500">${escapeHtml(field.error_text)}</span>` : ''}
                    </div>
                `;
            };

            const fieldHtml = (field, ticketId, pIdx) => {
                const key = field.key || field.label.toLowerCase().replace(/[^a-z0-9]+/g, '_');
                const name = `ticket_selections[${ticketId}][${pIdx}][${key}]`;
                const required = field.is_required ? 'required' : '';
                const label = `<label class="mb-1.5 block text-sm font-medium text-slate-700">${escapeHtml(field.label)}${field.is_required ? ' <span class="text-red-500">*</span>' : ''}</label>`;
                const inputCls = `block h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20`;
                const base = `name="${escapeHtml(name)}" ${required} class="${inputCls}" placeholder="${escapeHtml(field.placeholder || field.label)}"`;

                if (field.type === 'textarea') {
                    return `<div>${label}<textarea name="${escapeHtml(name)}" ${required} placeholder="${escapeHtml(field.placeholder || field.label)}" class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 min-h-[110px] resize-y"></textarea></div>`;
                }
                if (field.type === 'file') {
                    return fileUploadHtml(field, ticketId, pIdx);
                }
                if (['dropdown', 'radio', 'checkbox'].includes(field.type)) {
                    const options = Array.isArray(field.options) ? field.options : [];
                    if (field.type === 'dropdown') {
                        return `<div>${label}<select ${base}><option value="">Select an option</option>${options.map((option) => `<option>${escapeHtml(option)}</option>`).join('')}</select></div>`;
                    }
                    return `<div>${label}<div class="mt-2 space-y-2.5">${options.map((option) => `<label class="flex cursor-pointer items-center gap-3 text-sm text-slate-700"><input type="${field.type}" name="${escapeHtml(name)}${field.type === 'checkbox' ? '[]' : ''}" value="${escapeHtml(option)}" class="h-4 w-4 accent-indigo-600" ${field.type === 'radio' ? required : ''}><span>${escapeHtml(option)}</span></label>`).join('')}</div>${field.type === 'checkbox' && field.is_required ? `<input type="text" class="sr-only" tabindex="-1" aria-hidden="true" data-checkbox-required="${escapeHtml(name)}" required>` : ''}</div>`;
                }
                const type = ['email', 'number', 'date'].includes(field.type) ? field.type : 'text';
                return `<div>${label}<input type="${type}" ${base}></div>`;
            };

            const snapshotFormValues = () => {
                const saved = {};
                formTarget.querySelectorAll('input:not([type=hidden]):not([type=file]), select, textarea').forEach((el) => {
                    if (!el.name) return;
                    const match = el.name.match(/^ticket_selections\[(\d+)\]\[(\d+)\]\[(.+?)\](\[\])?$/);
                    if (!match) return;
                    const tId = match[1];
                    const pIdx = Number(match[2]);
                    const key = match[3];
                    if (!saved[tId]) saved[tId] = {};
                    if (!saved[tId][pIdx]) saved[tId][pIdx] = {};
                    if (el.type === 'checkbox') {
                        if (!saved[tId][pIdx][key]) saved[tId][pIdx][key] = { type: 'checkbox', values: [] };
                        if (el.checked) saved[tId][pIdx][key].values.push(el.value);
                    } else if (el.type === 'radio') {
                        if (el.checked) saved[tId][pIdx][key] = { type: 'radio', value: el.value };
                    } else {
                        saved[tId][pIdx][key] = { type: 'text', value: el.value };
                    }
                });
                return saved;
            };

            const restoreFormValues = (saved) => {
                Object.entries(saved).forEach(([tId, participants]) => {
                    Object.entries(participants).forEach(([pIdxStr, fields]) => {
                        const pIdx = Number(pIdxStr);
                        Object.entries(fields).forEach(([key, data]) => {
                            const prefix = `ticket_selections[${tId}][${pIdx}]`;
                            if (data.type === 'checkbox') {
                                formTarget.querySelectorAll(`input[name="${prefix}[${key}][]"]`).forEach((cb) => {
                                    cb.checked = data.values.includes(cb.value);
                                });
                            } else if (data.type === 'radio') {
                                const radio = formTarget.querySelector(`input[type="radio"][name="${prefix}[${key}]"][value="${data.value}"]`);
                                if (radio) radio.checked = true;
                            } else {
                                const el = formTarget.querySelector(`[name="${prefix}[${key}]"]`);
                                if (el) el.value = data.value;
                            }
                        });
                    });
                });
            };

            const refreshAccordionBadges = () => {
                formTarget.querySelectorAll('[data-accordion-item]').forEach((item) => {
                    const badge = item.querySelector('[data-accordion-badge]');
                    if (!badge) return;
                    const required = Array.from(item.querySelectorAll('input[required], select[required], textarea[required]'))
                        .filter((el) => !el.classList.contains('sr-only'));
                    const filled = required.length > 0 && required.every((el) => {
                        if (el.type === 'radio') return item.querySelector(`input[name="${CSS.escape(el.name)}"]:checked`) !== null;
                        return el.value.trim() !== '';
                    });
                    badge.classList.toggle('hidden', !filled);
                    badge.classList.toggle('flex', filled);
                });
            };

            const renderForms = (selections) => {
                const selKey = JSON.stringify(selections.map((s) => `${s.ticket.id}:${s.qty}`));
                const savedValues = formTarget.dataset.renderedSelections === selKey ? snapshotFormValues() : {};

                const allOpenEntries = selections.flatMap(({ ticket, qty }) => {
                    if (!ticket?.form || qty <= 1) return [];
                    return Array.from({ length: qty }, (_, pIdx) => `'${ticket.id}-${pIdx}': ${pIdx === 0}`);
                }).join(', ');

                const sectionsHtml = selections.map(({ ticket, qty }) => {
                    if (!ticket?.form) {
                        return `<div class="mb-6 overflow-hidden rounded-2xl border border-amber-200 bg-amber-50 px-6 py-4 text-sm text-amber-700"><strong>${escapeHtml(ticket.name)}</strong> — No registration form assigned for this ticket.</div>`;
                    }

                    const participantContent = qty === 1
                        ? `<div class="divide-y divide-slate-100"><div class="px-6 py-6"><div class="space-y-5">${(ticket.form.fields || []).map((f) => fieldHtml(f, ticket.id, 0)).join('')}</div></div></div>`
                        : (() => {
                            const items = Array.from({ length: qty }, (_, pIdx) => `
                                <div data-accordion-item="${ticket.id}-${pIdx}">
                                    <button type="button"
                                            @click="open['${ticket.id}-${pIdx}'] = !open['${ticket.id}-${pIdx}']; if (open['${ticket.id}-${pIdx}']) $nextTick(() => $el.closest('[data-accordion-item]').scrollIntoView({ behavior: 'smooth', block: 'nearest' }))"
                                            class="flex w-full items-center justify-between px-6 py-4 text-left transition hover:bg-slate-50 focus:outline-none">
                                        <span class="flex items-center gap-3">
                                            <svg :class="open['${ticket.id}-${pIdx}'] ? 'rotate-180' : ''" class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                                            <span class="text-sm font-semibold text-slate-700">Participant ${pIdx + 1}</span>
                                        </span>
                                        <span data-accordion-badge="${ticket.id}-${pIdx}" class="hidden h-5 w-5 items-center justify-center rounded-full bg-emerald-100">
                                            <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    </button>
                                    <div x-show="open['${ticket.id}-${pIdx}']" x-collapse>
                                        <div class="px-6 pb-6 pt-1"><div class="space-y-5">${(ticket.form.fields || []).map((f) => fieldHtml(f, ticket.id, pIdx)).join('')}</div></div>
                                    </div>
                                </div>
                            `).join('');
                            return `
                                <div>
                                    <div class="flex items-center gap-2 border-b border-slate-100 px-6 py-2.5">
                                        <button type="button" @click="Object.keys(open).filter(k => k.startsWith('${ticket.id}-')).forEach(k => open[k] = true)" class="rounded-md px-3 py-1.5 text-xs font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">Expand All</button>
                                        <span class="text-slate-200">|</span>
                                        <button type="button" @click="Object.keys(open).filter(k => k.startsWith('${ticket.id}-')).forEach(k => open[k] = false)" class="rounded-md px-3 py-1.5 text-xs font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">Collapse All</button>
                                    </div>
                                    <div class="divide-y divide-slate-100">${items}</div>
                                </div>
                            `;
                        })();

                    return `
                        <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="px-6 pt-6 pb-5">
                                <h3 class="text-lg font-semibold text-slate-900">${escapeHtml(ticket.name)}</h3>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">${escapeHtml(ticket.form.title)}</span>
                                    <span class="text-xs text-slate-400">${qty} ticket${qty > 1 ? 's' : ''}</span>
                                </div>
                            </div>
                            <hr class="border-slate-100">
                            ${participantContent}
                        </div>
                    `;
                }).join('');

                formTarget.innerHTML = `
                    <div x-data="{ open: { ${allOpenEntries} } }">
                        <form method="POST" action="${escapeHtml(multiSubmitUrl)}" enctype="multipart/form-data">
                            <input type="hidden" name="_token" value="${escapeHtml(csrf)}">
                            ${referral ? `<input type="hidden" name="referral" value="${escapeHtml(referral)}">` : ''}
                            ${sectionsHtml}
                            <div class="mt-2 flex justify-end">
                                <button type="submit" class="rounded-lg bg-slate-900 px-8 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-slate-800">Submit Registration</button>
                            </div>
                        </form>
                    </div>
                `;

                formTarget.dataset.renderedSelections = selKey;
                if (window.Alpine) window.Alpine.initTree(formTarget);
                if (Object.keys(savedValues).length) {
                    restoreFormValues(savedValues);
                    refreshAccordionBadges();
                }
                formTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
            };

            root.addEventListener('change', (event) => {
                const fileInput = event.target.closest('[data-file-input]');
                if (fileInput) {
                    const wrapper = fileInput.closest('[data-file-upload]');
                    const file = fileInput.files?.[0];
                    const defaultState = wrapper.querySelector('[data-upload-default]');
                    const loadingState = wrapper.querySelector('[data-upload-loading]');
                    const successState = wrapper.querySelector('[data-upload-success]');
                    const loadingName = wrapper.querySelector('[data-upload-loading-name]');
                    const fileName = wrapper.querySelector('[data-upload-file-name]');
                    const progress = wrapper.querySelector('[data-upload-progress]');
                    const actions = wrapper.querySelector('[data-upload-actions]');

                    if (!file) return;
                    if (wrapper.dataset.uploading === 'true') return;

                    wrapper.dataset.uploading = 'true';
                    defaultState.classList.add('hidden');
                    successState.classList.add('hidden');
                    actions.classList.add('hidden');
                    actions.classList.remove('flex');
                    loadingState.classList.remove('hidden');
                    loadingName.textContent = file.name;
                    progress.style.width = '45%';

                    window.setTimeout(() => { progress.style.width = '100%'; }, 120);
                    window.setTimeout(() => {
                        wrapper.dataset.uploading = 'false';
                        loadingState.classList.add('hidden');
                        successState.classList.remove('hidden');
                        fileName.textContent = file.name;
                        actions.classList.remove('hidden');
                        actions.classList.add('flex');
                    }, 520);

                    return;
                }

                const marker = event.target.closest('input[type="checkbox"]');
                if (marker) {
                    const name = marker.name.replace(/\[\]$/, '');
                    const requiredMarker = root.querySelector(`[data-checkbox-required="${CSS.escape(name)}"]`);
                    if (requiredMarker) {
                        requiredMarker.value = root.querySelectorAll(`input[name="${CSS.escape(marker.name)}"]:checked`).length ? 'selected' : '';
                    }
                }
                updateAccordionBadge(event.target);
            });

            root.addEventListener('submit', (event) => {
                const form = event.target.closest('form');
                if (!form) return;
                const button = form.querySelector('button[type="submit"]');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
                if (button) {
                    button.disabled = true;
                    button.textContent = 'Submitting...';
                    button.classList.add('opacity-70', 'cursor-wait');
                }
            });

            root.addEventListener('dragover', (event) => {
                const dropzone = event.target.closest('[data-upload-dropzone]');
                if (!dropzone) return;
                event.preventDefault();
                dropzone.classList.add('border-blue-400', 'bg-blue-50');
            });

            root.addEventListener('dragleave', (event) => {
                const dropzone = event.target.closest('[data-upload-dropzone]');
                if (!dropzone) return;
                dropzone.classList.remove('border-blue-400', 'bg-blue-50');
            });

            root.addEventListener('drop', (event) => {
                const dropzone = event.target.closest('[data-upload-dropzone]');
                if (!dropzone) return;
                event.preventDefault();
                dropzone.classList.remove('border-blue-400', 'bg-blue-50');
                const input = dropzone.querySelector('[data-file-input]');
                if (!input || input.closest('[data-file-upload]')?.dataset.uploading === 'true' || !event.dataTransfer?.files?.length) return;
                input.files = event.dataTransfer.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });

            root.addEventListener('click', (event) => {
                const replaceFile = event.target.closest('[data-replace-file]');
                const removeFile = event.target.closest('[data-remove-file]');
                if (replaceFile || removeFile) {
                    const wrapper = event.target.closest('[data-file-upload]');
                    if (wrapper.dataset.uploading === 'true') return;
                    const fileInput = wrapper.querySelector('[data-file-input]');
                    const defaultState = wrapper.querySelector('[data-upload-default]');
                    const loadingState = wrapper.querySelector('[data-upload-loading]');
                    const successState = wrapper.querySelector('[data-upload-success]');
                    const actions = wrapper.querySelector('[data-upload-actions]');
                    const progress = wrapper.querySelector('[data-upload-progress]');

                    if (removeFile) {
                        fileInput.value = '';
                        wrapper.dataset.uploading = 'false';
                        progress.style.width = '0%';
                        loadingState.classList.add('hidden');
                        successState.classList.add('hidden');
                        defaultState.classList.remove('hidden');
                        actions.classList.add('hidden');
                        actions.classList.remove('flex');
                    }
                    if (replaceFile) fileInput.click();
                    return;
                }

                const dec = event.target.closest('[data-qty-dec]');
                const inc = event.target.closest('[data-qty-inc]');
                if (dec || inc) {
                    const id = (dec || inc).dataset[dec ? 'qtyDec' : 'qtyInc'];
                    const input = root.querySelector(`[data-ticket-quantity="${id}"]`);
                    if (input) {
                        const min = input.min !== '' ? Number(input.min) : 0;
                        const max = Number(input.max) || 99;
                        const next = Math.min(max, Math.max(min, Number(input.value) + (dec ? -1 : 1)));
                        input.value = next;
                        syncRegisterBtn();
                    }
                    return;
                }

                if (event.target.closest('[data-back-to-tickets]')) {
                    const ticketStep = root.querySelector('[data-ticket-step]');
                    const formStep = root.querySelector('[data-form-step]');
                    if (formStep) formStep.classList.add('hidden');
                    if (ticketStep) ticketStep.style.display = '';
                    root.querySelector('#tickets')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    return;
                }

                if (event.target.closest('#ticket-register-btn')) {
                    const selections = tickets
                        .map((t) => ({ ticket: t, qty: Number(root.querySelector(`[data-ticket-quantity="${t.id}"]`)?.value) || 0 }))
                        .filter((s) => s.qty > 0);
                    if (!selections.length) return;
                    const ticketStep = root.querySelector('[data-ticket-step]');
                    const formStep = root.querySelector('[data-form-step]');
                    if (ticketStep) ticketStep.style.display = 'none';
                    if (formStep) formStep.classList.remove('hidden');
                    renderForms(selections);
                    return;
                }
            });

            const syncRegisterBtn = () => {
                const wrap = root.querySelector('#ticket-register-wrap');
                if (!wrap) return;
                const hasQty = Array.from(root.querySelectorAll('[data-ticket-quantity]')).some((el) => Number(el.value) > 0);
                wrap.style.display = hasQty ? 'flex' : 'none';
            };

            const updateAccordionBadge = (target) => {
                const item = target.closest('[data-accordion-item]');
                if (!item) return;
                const badge = item.querySelector('[data-accordion-badge]');
                if (!badge) return;
                const required = Array.from(item.querySelectorAll('input[required], select[required], textarea[required]'))
                    .filter((el) => !el.classList.contains('sr-only'));
                const filled = required.length > 0 && required.every((el) => {
                    if (el.type === 'radio') return item.querySelector(`input[name="${CSS.escape(el.name)}"]:checked`) !== null;
                    return el.value.trim() !== '';
                });
                badge.classList.toggle('hidden', !filled);
                badge.classList.toggle('flex', filled);
            };

            root.addEventListener('input', (event) => {
                if (event.target.matches('[data-ticket-quantity]')) syncRegisterBtn();
                updateAccordionBadge(event.target);
            });

            const oldSelectionsRaw = root.dataset.oldSelections;
            if (oldSelectionsRaw) {
                try {
                    const oldSelections = JSON.parse(oldSelectionsRaw);
                    const entries = Object.entries(oldSelections || {});
                    if (entries.length) {
                        const selections = entries
                            .map(([ticketId, participants]) => ({
                                ticket: tickets.find((t) => String(t.id) === String(ticketId)),
                                qty: Object.keys(participants).length,
                            }))
                            .filter((s) => s.ticket && s.qty > 0);

                        if (selections.length) {
                            selections.forEach(({ ticket, qty }) => {
                                const qtyInput = root.querySelector(`[data-ticket-quantity="${ticket.id}"]`);
                                if (qtyInput) qtyInput.value = qty;
                            });
                            syncRegisterBtn();

                            const ticketStep = root.querySelector('[data-ticket-step]');
                            const formStep = root.querySelector('[data-form-step]');
                            if (ticketStep) ticketStep.style.display = 'none';
                            if (formStep) formStep.classList.remove('hidden');

                            renderForms(selections);

                            entries.forEach(([ticketId, participants]) => {
                                Object.entries(participants).forEach(([pIdx, fields]) => {
                                    Object.entries(fields).forEach(([key, value]) => {
                                        const prefix = `ticket_selections[${ticketId}][${pIdx}]`;
                                        if (Array.isArray(value)) {
                                            value.forEach((v) => {
                                                const cb = formTarget.querySelector(`input[type="checkbox"][name="${prefix}[${key}][]"][value="${v}"]`);
                                                if (cb) cb.checked = true;
                                            });
                                        } else {
                                            const radio = formTarget.querySelector(`input[type="radio"][name="${prefix}[${key}]"][value="${value}"]`);
                                            if (radio) {
                                                radio.checked = true;
                                            } else {
                                                const el = formTarget.querySelector(`[name="${prefix}[${key}]"]`);
                                                if (el && el.type !== 'file') el.value = value;
                                            }
                                        }
                                    });
                                });
                            });
                            refreshAccordionBadges();
                        }
                    }
                } catch (e) {}
            }
        })();
    </script>
</body>
</html>
