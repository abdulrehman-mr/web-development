@props(['title' => 'Dashboard', 'actions' => null])
@php
    $links = [
        ['Dashboard', 'admin.dashboard', 'grid', 'admin.dashboard'],
        ['Bookings', 'admin.bookings.index', 'ticket', 'admin.bookings.*'],
        ['Enquiries', 'admin.inquiries.index', 'inbox', 'admin.inquiries.*'],
        null,
        ['Journeys', 'admin.packages.index', 'map-pin', 'admin.packages.*'],
        ['Destinations', 'admin.destinations.index', 'mountain', 'admin.destinations.*'],
        ['Stays', 'admin.hotels.index', 'bed', 'admin.hotels.*'],
        ['Experiences', 'admin.categories.index', 'tag', 'admin.categories.*'],
        null,
        ['Reviews', 'admin.testimonials.index', 'quote', 'admin.testimonials.*'],
        ['FAQs', 'admin.faqs.index', 'help', 'admin.faqs.*'],
        ['Site content', 'admin.settings.edit', 'settings', 'admin.settings.*'],
    ];
    $pending = \App\Models\Booking::where('status', 'pending')->count();
    $unread = \App\Models\Inquiry::where('is_read', false)->count();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} · Admin · {{ setting('site_name', 'Safarnama') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-marble-100" x-data="{ nav: false }">
<div class="min-h-screen lg:grid lg:grid-cols-[16rem_1fr]">
    {{-- Sidebar --}}
    <aside :class="nav ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-pine-950 text-marble-50/80 transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
        <div class="flex h-16 items-center justify-between px-5">
            <a href="{{ route('admin.dashboard') }}" class="font-display text-2xl font-semibold text-marble-50">{{ setting('site_name', 'Safarnama') }}</a>
            <button type="button" class="lg:hidden" @click="nav = false" aria-label="Close menu"><x-icon name="x"/></button>
        </div>
        <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-2 text-sm">
            @foreach ($links as $link)
                @if (! $link)
                    <div class="mx-3 my-3 border-t border-marble-50/10"></div>
                    @continue
                @endif
                @php [$label, $route, $icon, $pattern] = $link; $active = request()->routeIs($pattern); @endphp
                <a href="{{ route($route) }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 transition', $active ? 'bg-marble-50/10 font-semibold text-marble-50' : 'hover:bg-marble-50/5 hover:text-marble-50'])>
                    <x-icon :name="$icon" @class(['size-5', $active ? 'text-brass-400' : 'text-marble-50/50'])/>
                    <span class="flex-1">{{ $label }}</span>
                    @if ($label === 'Bookings' && $pending)<span class="rounded-full bg-brass-400 px-2 py-0.5 text-xs font-semibold text-pine-950">{{ $pending }}</span>@endif
                    @if ($label === 'Enquiries' && $unread)<span class="rounded-full bg-brass-400 px-2 py-0.5 text-xs font-semibold text-pine-950">{{ $unread }}</span>@endif
                </a>
            @endforeach
        </nav>
        <div class="border-t border-marble-50/10 p-3 text-sm">
            <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-marble-50/5"><x-icon name="external" class="size-5 text-marble-50/50"/>View website</a>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-marble-50/5"><x-icon name="logout" class="size-5 text-marble-50/50"/>Sign out <span class="ml-auto truncate text-xs text-marble-50/40">{{ auth()->user()->name }}</span></button>
            </form>
        </div>
    </aside>
    <div x-cloak x-show="nav" x-transition.opacity @click="nav = false" class="fixed inset-0 z-40 bg-pine-950/50 lg:hidden"></div>

    {{-- Content --}}
    <div class="min-w-0">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-marble-200 bg-marble-50/90 px-4 backdrop-blur sm:px-8">
            <button type="button" class="grid size-10 place-items-center rounded-xl ring-1 ring-marble-300 lg:hidden" @click="nav = true" aria-label="Open menu"><x-icon name="menu"/></button>
            <h1 class="truncate text-lg font-semibold text-pine-950">{{ $title }}</h1>
            <div class="ml-auto flex items-center gap-2">{{ $actions }}</div>
        </header>
        <main class="p-4 sm:p-8">
            @if (session('status'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition class="mb-6 flex items-center gap-3 rounded-2xl bg-pine-800 px-4 py-3 text-sm text-marble-50" role="status">
                    <x-icon name="check" class="size-5 text-brass-400"/>{{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-2xl bg-sandstone-500/10 p-4 text-sm text-sandstone-600" role="alert">
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
