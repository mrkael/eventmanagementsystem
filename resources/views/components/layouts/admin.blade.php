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
        $navItems = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'permission' => 'dashboard.view', 'icon' => 'dashboard'],
            ['label' => 'Organiser Profile', 'route' => 'core.organisers.index', 'permission' => 'organisers.view', 'icon' => 'building'],
            ['label' => 'Events', 'route' => 'core.events.index', 'permission' => 'events.view', 'icon' => 'calendar'],
            ['label' => 'Attendees Management', 'route' => 'core.attendees.index', 'permission' => 'registrations.view', 'icon' => 'users'],
            ['label' => 'Emails', 'route' => 'core.emails.index', 'permission' => 'emails.view', 'icon' => 'mail'],
        ];
    @endphp

    <div id="admin-shell" class="min-h-screen p-3 lg:grid lg:grid-cols-[18rem_1fr] lg:gap-4 lg:p-4">
        <div data-sidebar-overlay class="fixed inset-0 z-30 hidden bg-slate-950/40 backdrop-blur-sm lg:hidden"></div>

        <aside data-sidebar class="ds-glass fixed inset-y-3 left-3 z-40 flex w-[18rem] -translate-x-[calc(100%+1rem)] flex-col rounded-[28px] transition-transform duration-300 lg:sticky lg:top-4 lg:h-[calc(100vh-2rem)] lg:translate-x-0">
            <div class="p-4">
                <div class="flex items-center gap-3 rounded-[22px] bg-slate-950 p-3 text-white shadow-soft">
                    <div class="grid size-11 place-items-center rounded-2xl bg-white/12 text-sm font-black">EM</div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold">EventOS</p>
                        <p class="text-xs text-white/58">Premium workspace</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 space-y-1 px-3">
                @foreach ($navItems as $item)
                    @if (auth()->user()->hasPermission($item['permission']))
                        @php($active = request()->routeIs($item['route']) || request()->routeIs(\Illuminate\Support\Str::beforeLast($item['route'], '.') . '.*'))
                        <a href="{{ route($item['route']) }}" class="group flex min-h-12 items-center gap-3 rounded-2xl px-3 text-sm font-bold transition duration-200 {{ $active ? 'bg-slate-950 text-white shadow-soft' : 'text-slate-600 hover:bg-white hover:text-slate-950 hover:shadow-sm' }}">
                            <x-ui.icon :name="$item['icon']" class="size-5 shrink-0" />
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="p-4">
                <div class="rounded-[22px] border border-slate-200/80 bg-white/70 p-3">
                    <p class="truncate text-sm font-bold">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="ds-button-secondary w-full">Sign out</button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="sticky top-3 z-20 mb-4 rounded-[28px] border border-white/70 bg-white/78 px-4 py-3 shadow-soft backdrop-blur-2xl lg:top-4">
                <div class="flex items-center gap-3">
                    <button type="button" data-sidebar-open class="grid size-11 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-700 lg:hidden" aria-label="Open sidebar">
                        <span class="h-0.5 w-5 rounded bg-current shadow-[0_6px_0_current,0_-6px_0_current]"></span>
                    </button>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold uppercase text-blue-600">{{ $eyebrow ?? 'Workspace' }}</p>
                        <h1 class="truncate text-lg font-bold text-slate-950">{{ $heading ?? $title ?? 'Dashboard' }}</h1>
                    </div>
                    <div class="hidden min-h-11 items-center rounded-full border border-slate-200 bg-slate-50 px-3 text-sm text-slate-500 md:flex">
                        <x-ui.icon name="search" class="mr-2 size-4" />
                        Search foundation
                        <span class="ml-3 rounded-full border border-slate-200 bg-white px-2 py-0.5 text-xs font-bold">Ctrl K</span>
                    </div>
                </div>
            </header>

            <main id="main-content" class="ds-page-enter px-1 pb-8 lg:px-2">
                @if (session('status'))
                    <div role="status" aria-live="polite" class="mb-5 rounded-[20px] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div role="alert" class="mb-5 rounded-[20px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <p class="font-bold">Please review the highlighted issues.</p>
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
                sidebar.classList.remove('-translate-x-[calc(100%+1rem)]');
                overlay.classList.remove('hidden');
            };
            const closeSidebar = () => {
                sidebar.classList.add('-translate-x-[calc(100%+1rem)]');
                overlay.classList.add('hidden');
            };
            openButton?.addEventListener('click', openSidebar);
            overlay?.addEventListener('click', closeSidebar);
        });
    </script>
</body>
</html>
