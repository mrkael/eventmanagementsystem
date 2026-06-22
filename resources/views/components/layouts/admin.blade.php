<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Event Management System' }}</title>
    @include('partials.assets')
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-emerald-800 focus:shadow">Skip to main content</a>
    <div id="admin-shell" class="min-h-screen lg:flex">
        <div data-sidebar-overlay class="fixed inset-0 z-30 hidden bg-slate-950/50 lg:hidden"></div>

        <aside
            data-sidebar
            class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white transition-all duration-200 lg:static lg:translate-x-0"
        >
            <div class="flex h-16 items-center gap-3 border-b border-slate-200 px-4">
                <div class="grid size-10 place-items-center rounded-lg bg-emerald-700 text-sm font-bold text-white">EMS</div>
                <div class="min-w-0 sidebar-label">
                    <p class="text-sm font-semibold leading-5">Event Management</p>
                    <p class="text-xs text-slate-500">Enterprise Console</p>
                </div>
                <button type="button" data-sidebar-collapse class="ml-auto hidden size-10 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 lg:grid" aria-label="Toggle sidebar">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>

            @php
                $navItems = [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'permission' => 'dashboard.view', 'icon' => 'M4 13h6V4H4v9Zm0 7h6v-5H4v5Zm10 0h6v-9h-6v9Zm0-11h6V4h-6v5Z'],
                    ['label' => 'Organiser Profile', 'route' => 'core.organisers.index', 'permission' => 'organisers.view', 'icon' => 'M3 21h18M5 21V7l8-4 6 4v14M9 10h1M9 14h1M13 10h1M13 14h1'],
                    ['label' => 'Contacts', 'route' => 'core.contacts.index', 'permission' => 'contacts.view', 'icon' => 'M16 4h2a2 2 0 0 1 2 2v14H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2M9 2h6v4H9zM9 12h6M9 16h4'],
                    ['label' => 'Events', 'route' => 'core.events.index', 'permission' => 'events.view', 'icon' => 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z'],
                    ['label' => 'Attendees', 'route' => 'core.attendees.index', 'permission' => 'registrations.view', 'icon' => 'M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3ZM8 11c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3ZM8 13c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4ZM16 13c-.31 0-.66.02-1.02.05 1.32.95 2.02 2.21 2.02 3.95v2h7v-2c0-2.66-5.33-4-8-4Z'],
                    ['label' => 'Emails', 'route' => 'core.emails.index', 'permission' => 'emails.view', 'icon' => 'M4 4h16v16H4zM4 7l8 6 8-6'],
                    ['label' => 'Team Management', 'route' => 'admin.users.index', 'permission' => 'users.view', 'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75'],
                    ['label' => 'Roles', 'route' => 'admin.roles.index', 'permission' => 'roles.view', 'icon' => 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75'],
                    ['label' => 'Permissions', 'route' => 'admin.permissions.index', 'permission' => 'permissions.view', 'icon' => 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z'],
                    ['label' => 'Audit Logs', 'route' => 'admin.audit-logs.index', 'permission' => 'audit_logs.view', 'icon' => 'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
                    ['label' => 'Profile', 'route' => 'profile.edit', 'permission' => 'profile.update', 'icon' => 'M20 21a8 8 0 1 0-16 0M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z'],
                ];
            @endphp

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                @foreach ($navItems as $item)
                    @if (auth()->user()->hasPermission($item['permission']))
                        <a href="{{ route($item['route']) }}" class="group flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-medium transition {{ request()->routeIs($item['route']) || request()->routeIs(\Illuminate\Support\Str::beforeLast($item['route'], '.') . '.*') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">
                            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $item['icon'] }}"/></svg>
                            <span class="sidebar-label">{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="border-t border-slate-200 p-4">
                <div class="mb-3 min-w-0 sidebar-label">
                    <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex min-h-11 w-full items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                        <span class="sidebar-label">Sign out</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col">
            <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur lg:px-6">
                <button type="button" data-sidebar-open class="grid size-10 place-items-center rounded-lg text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Open sidebar">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="text-lg font-semibold">{{ $heading ?? $title ?? 'Dashboard' }}</h1>
                    <p class="hidden text-sm text-slate-500 sm:block">{{ $subheading ?? 'Core setup and access control' }}</p>
                </div>
            </header>

            <main id="main-content" class="flex-1 px-4 py-6 lg:px-8">
                @if (session('status'))
                    <div role="status" aria-live="polite" class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <p class="font-semibold">Please review the highlighted issues.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const shell = document.getElementById('admin-shell');
            const sidebar = document.querySelector('[data-sidebar]');
            const overlay = document.querySelector('[data-sidebar-overlay]');
            const openButton = document.querySelector('[data-sidebar-open]');
            const collapseButton = document.querySelector('[data-sidebar-collapse]');

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
            collapseButton?.addEventListener('click', () => {
                sidebar.classList.toggle('lg:w-20');
                sidebar.classList.toggle('lg:w-72');
                shell.classList.toggle('sidebar-collapsed');
            });
        });
    </script>
</body>
</html>
