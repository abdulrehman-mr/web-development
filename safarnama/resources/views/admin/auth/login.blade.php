<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Sign in · {{ setting('site_name', 'Safarnama') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="jali-bg grid min-h-screen place-items-center bg-pine-950 p-4">
    <div class="w-full max-w-sm">
        <p class="mb-8 text-center font-display text-4xl font-semibold text-marble-50">{{ setting('site_name', 'Safarnama') }}</p>
        <form method="post" action="{{ url('/admin/login') }}" class="space-y-5 rounded-3xl bg-marble-50 p-8 shadow-2xl">
            @csrf
            <div>
                <h1 class="text-lg font-semibold text-pine-950">Sign in to the admin panel</h1>
                <p class="mt-1 text-sm text-ink/55">Manage journeys, bookings and site content.</p>
            </div>
            <x-admin.input name="email" type="email" label="Email" required autofocus autocomplete="username"/>
            <x-admin.input name="password" type="password" label="Password" required autocomplete="current-password"/>
            <label class="flex items-center gap-2 text-sm text-ink/70"><input type="checkbox" name="remember" class="size-4 rounded accent-pine-700"> Keep me signed in</label>
            <button class="btn btn-primary w-full">Sign in</button>
        </form>
        <p class="mt-6 text-center text-sm"><a href="{{ route('home') }}" class="text-marble-50/60 hover:text-marble-50">← Back to website</a></p>
    </div>
</body>
</html>
