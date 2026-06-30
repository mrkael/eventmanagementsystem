<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Event Management Platform' }}</title>
    @include('partials.assets')
</head>
<body class="ds-shell-bg min-h-screen font-sans text-slate-950 antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-5 focus:top-5 focus:z-50 focus:rounded-full focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-slate-950 focus:shadow-lg">Skip to main content</a>

    @php
        $navGroups = [
            [
                'label' => null,
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'permission' => 'dashboard.view', 'icon' => 'dashboard'],
                ],
            ],
            [
                'label' => 'Management',
                'items' => [
                    ['label' => 'Organiser Profile', 'route' => 'core.organisers.index', 'permission' => 'organisers.view', 'icon' => 'building'],
                    ['label' => 'Events',             'route' => 'core.events.index',      'permission' => 'events.view',        'icon' => 'calendar'],
                    ['label' => 'Attendees',          'route' => 'core.attendees.index',   'permission' => 'registrations.view', 'icon' => 'users'],
                    ['label' => 'Emails',             'route' => 'core.emails.index',      'permission' => 'emails.view',        'icon' => 'mail'],
                ],
            ],
        ];
    @endphp

    <div data-sidebar-overlay class="fixed inset-0 z-30 hidden bg-slate-950/40 backdrop-blur-sm lg:hidden"></div>

    <div class="flex min-h-screen">

        {{-- ── Sidebar ────────────────────────────────────────────── --}}
        <aside
            data-sidebar
            class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 -translate-x-full flex-col border-r border-slate-200/80 bg-white shadow-[1px_0_0_0_rgb(0,0,0,0.04)] transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
        >
            {{-- Brand --}}
            <div class="shrink-0 px-4 pb-4 pt-5">
                <div class="flex items-center gap-3 rounded-2xl bg-[#002169] px-4 py-3">
                    <div class="grid size-9 shrink-0 place-items-center rounded-xl bg-white/15 text-xs font-black text-white">EM</div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold leading-none text-white">EventOS</p>
                        <p class="mt-1 text-[11px] font-medium text-white/55">Premium workspace</p>
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-3 py-1">
                @foreach ($navGroups as $group)
                    @if ($group['label'])
                        <div class="px-2 pb-1.5 pt-5">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.08em] text-slate-400">{{ $group['label'] }}</p>
                        </div>
                    @endif

                    <ul class="space-y-0.5">
                        @foreach ($group['items'] as $item)
                            @if (auth()->user()->hasPermission($item['permission']))
                                @php($active = request()->routeIs($item['route']) || request()->routeIs(\Illuminate\Support\Str::beforeLast($item['route'], '.') . '.*'))
                                <li>
                                    <a
                                        href="{{ route($item['route']) }}"
                                        class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-200 {{ $active ? 'bg-[#002169]/[.07] font-semibold text-[#002169]' : 'font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                                        @if($active) aria-current="page" @endif
                                    >
                                        @if ($active)
                                            <span class="absolute inset-y-2 left-0 w-[3px] rounded-full bg-[#002169]" aria-hidden="true"></span>
                                        @endif
                                        <x-ui.icon
                                            :name="$item['icon']"
                                            class="size-5 shrink-0 transition-colors duration-200 {{ $active ? 'text-[#002169]' : 'text-slate-400 group-hover:text-slate-600' }}"
                                        />
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endforeach
            </nav>

            {{-- Footer: user profile --}}
            <div class="shrink-0 px-3 pb-5">
                <div class="mb-3 h-px bg-slate-200/80"></div>
                <div class="flex items-center gap-3 rounded-xl px-2 py-2">
                    <div class="grid size-8 shrink-0 place-items-center rounded-full bg-[#002169] text-[11px] font-bold text-white">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                        <p class="truncate text-[11px] text-slate-500">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="grid size-8 place-items-center rounded-lg text-slate-400 transition-colors duration-150 hover:bg-red-50 hover:text-red-600"
                            aria-label="Sign out"
                        >
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ── Main content ────────────────────────────────────────── --}}
        <div class="flex min-w-0 flex-1 flex-col">

            <header class="sticky top-0 z-20 border-b border-slate-200/70 bg-white/95 px-6 py-3.5 backdrop-blur-md">
                <div class="flex items-center gap-4">
                    <button
                        type="button"
                        data-sidebar-open
                        class="grid size-9 place-items-center rounded-lg border border-slate-200 text-slate-600 transition-colors duration-150 hover:bg-slate-100 lg:hidden"
                        aria-label="Open sidebar"
                    >
                        <span class="h-0.5 w-4 rounded bg-current shadow-[0_5px_0_currentColor,0_-5px_0_currentColor]"></span>
                    </button>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-[#002169]/70">{{ $eyebrow ?? 'Workspace' }}</p>
                        <h1 class="truncate text-lg font-bold text-slate-950">{{ $heading ?? $title ?? 'Dashboard' }}</h1>
                    </div>
                    <div class="hidden items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-400 md:flex">
                        <x-ui.icon name="search" class="size-4 shrink-0" />
                        <span>Search</span>
                        <span class="ml-2 rounded border border-slate-200 bg-white px-1.5 py-0.5 text-[10px] font-bold text-slate-500">Ctrl K</span>
                    </div>
                </div>
            </header>

            <main id="main-content" class="ds-page-enter flex-1 px-4 py-5 lg:px-6 lg:py-6">
                @if (session('status'))
                    <div role="status" aria-live="polite" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
                @endif

                @if (session('warning'))
                    <div role="status" aria-live="polite" class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">{{ session('warning') }}</div>
                @endif

                @if ($errors->any())
                    <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <p class="font-semibold">Please review the highlighted issues.</p>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.querySelector('[data-sidebar]');
            const overlay = document.querySelector('[data-sidebar-overlay]');
            const openButton = document.querySelector('[data-sidebar-open]');
            const openSidebar = () => {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            };
            const closeSidebar = () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            };
            openButton?.addEventListener('click', openSidebar);
            overlay?.addEventListener('click', closeSidebar);
        });
    </script>
</body>
</html>
