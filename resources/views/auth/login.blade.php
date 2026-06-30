<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | Event Management System</title>
    @include('partials.assets')
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <main class="grid min-h-screen place-items-center px-4 py-10">
        <section class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
            <div class="mb-8">
                <div class="mb-4 grid size-12 place-items-center rounded-lg bg-emerald-700 text-sm font-bold text-white">EMS</div>
                <h1 class="text-2xl font-bold">Sign in</h1>
                <p class="mt-2 text-sm text-slate-600">Access the event management console.</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                    @error('email') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                    @error('password') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                    Remember this device
                </label>

                <button type="submit" class="btn btn-primary btn-md w-full">Sign in</button>
            </form>

            @if (config('event_management.self_registration_enabled'))
                <p class="mt-6 text-center text-sm text-slate-600">
                    Need an account?
                    <a href="{{ route('register') }}" class="font-semibold text-emerald-700 hover:text-emerald-800">Create one</a>
                </p>
            @endif
        </section>
    </main>
</body>
</html>
