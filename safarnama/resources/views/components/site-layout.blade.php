@props(['title' => null, 'description' => null, 'transparent' => true])
@php
    $siteName = setting('site_name', 'Safarnama');
    $nav = [
        ['Journeys', route('packages.index'), 'packages.*'],
        ['Destinations', route('destinations.index'), 'destinations.*'],
        ['Stays', route('stays'), 'stays'],
        ['About', route('about'), 'about'],
        ['Contact', route('contact'), 'contact'],
    ];
    $experiences = \App\Models\Category::orderBy('sort_order')->get(['name', 'slug', 'tagline']);
    $whatsapp = setting('contact_whatsapp');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · '.$siteName : $siteName.' — '.setting('site_tagline') }}</title>
    <meta name="description" content="{{ $description ?? setting('hero_subtitle') }}">
    <meta property="og:title" content="{{ $title ?? $siteName }}">
    <meta property="og:description" content="{{ $description ?? setting('site_tagline') }}">
    <meta name="theme-color" content="#0e2c25">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%230e2c25'/%3E%3Cpath d='M16 5c1 3 4 5 7 6.5V27H9V11.5C12 10 15 8 16 5Z' fill='%23d3a955'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:rounded-full focus:bg-brass-400 focus:px-4 focus:py-2 focus:text-pine-950">Skip to content</a>

{{-- Header --}}
<header x-data="{ open: false, scrolled: false, exp: false }"
        x-init="scrolled = window.scrollY > 24"
        @scroll.window="scrolled = window.scrollY > 24"
        @keydown.escape.window="open = false; exp = false"
        :class="scrolled || open || {{ $transparent ? 'false' : 'true' }} ? 'bg-pine-950/95 shadow-lg shadow-pine-950/10 backdrop-blur' : 'bg-transparent'"
        class="fixed inset-x-0 top-0 z-50 transition-colors duration-300">
    <div class="container-x flex h-18 items-center justify-between gap-6 py-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-marble-50" aria-label="{{ $siteName }} home">
            @if (setting('logo'))
                <img src="{{ media_url(setting('logo')) }}" alt="{{ $siteName }}" class="h-9 w-auto">
            @else
                <svg viewBox="0 0 32 40" class="h-9 w-7 text-brass-400" aria-hidden="true"><path d="M16 1c1.3 4 5.3 7.3 10 9.5C29 12 31 15 31 18.5V39H1V18.5C1 15 3 12 6 10.5 10.7 8.3 14.7 5 16 1Z" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M16 9c.8 2.5 3.4 4.6 6.2 6 1.8.9 2.8 2.6 2.8 4.5V39H7V19.5c0-1.9 1-3.6 2.8-4.5 2.8-1.4 5.4-3.5 6.2-6Z" fill="currentColor" opacity=".25"/></svg>
                <span class="font-display text-2xl font-semibold tracking-wide">{{ $siteName }}</span>
            @endif
        </a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Main">
            <div class="relative" @mouseenter="exp = true" @mouseleave="exp = false">
                <button type="button" @click="exp = !exp" :aria-expanded="exp" class="inline-flex items-center gap-1 rounded-full px-4 py-2 text-sm font-medium text-marble-50/85 transition hover:text-marble-50">
                    Experiences <x-icon name="chevron-down" class="size-4 transition" ::class="exp && 'rotate-180'"/>
                </button>
                <div x-cloak x-show="exp" x-transition.opacity.duration.150ms class="absolute top-full left-0 w-[34rem] pt-3">
                    <div class="grid grid-cols-2 gap-1 rounded-3xl bg-marble-50 p-3 shadow-2xl ring-1 ring-marble-200">
                        @foreach ($experiences as $exp)
                            <a href="{{ route('packages.index', ['category' => $exp->slug]) }}" class="rounded-2xl p-3 transition hover:bg-marble-100">
                                <span class="block font-display text-lg font-semibold text-pine-950">{{ $exp->name }}</span>
                                <span class="mt-0.5 block text-xs leading-snug text-ink/60">{{ $exp->tagline }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @foreach ($nav as [$label, $url, $pattern])
                <a href="{{ $url }}" @class(['rounded-full px-4 py-2 text-sm font-medium transition', request()->routeIs($pattern) ? 'text-brass-300' : 'text-marble-50/85 hover:text-marble-50'])>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ route('contact') }}" class="btn btn-brass hidden px-5 py-2.5 sm:inline-flex">Plan my trip</a>
            <button type="button" @click="open = !open" class="grid size-11 place-items-center rounded-full text-marble-50 ring-1 ring-marble-50/25 lg:hidden" :aria-expanded="open" aria-controls="mobile-nav">
                <span class="sr-only">Menu</span>
                <x-icon name="menu" x-show="!open"/>
                <x-icon name="x" x-show="open" x-cloak/>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div id="mobile-nav" x-cloak x-show="open" x-collapse class="border-t border-marble-50/10 lg:hidden">
        <nav class="container-x max-h-[calc(100dvh-4.5rem)] overflow-y-auto py-6" aria-label="Mobile">
            @foreach ($nav as [$label, $url])
                <a href="{{ $url }}" class="block border-b border-marble-50/10 py-4 font-display text-2xl text-marble-50">{{ $label }}</a>
            @endforeach
            <p class="eyebrow mt-6 mb-3 text-brass-300">Experiences</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($experiences as $exp)
                    <a href="{{ route('packages.index', ['category' => $exp->slug]) }}" class="rounded-2xl bg-marble-50/5 p-3 text-sm text-marble-50/85">{{ $exp->name }}</a>
                @endforeach
            </div>
            <a href="{{ route('contact') }}" class="btn btn-brass mt-6 w-full">Plan my trip</a>
        </nav>
    </div>
</header>

<main id="main">
    {{ $slot }}
</main>

{{-- Footer --}}
<footer class="jali-bg relative bg-pine-950 text-marble-50/75">
    <div class="container-x py-16 sm:py-20">
        <div class="grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <p class="font-display text-3xl font-semibold text-marble-50">{{ $siteName }}</p>
                <p class="mt-3 max-w-sm text-sm leading-relaxed">{{ setting('site_tagline') }}. Private, fully guided journeys for travellers from Canada, the UK, the US and beyond.</p>
                <div class="mt-6 flex gap-2">
                    @foreach (['instagram', 'facebook', 'youtube'] as $network)
                        @if (setting('social_'.$network))
                            <a href="{{ setting('social_'.$network) }}" target="_blank" rel="noopener" class="grid size-10 place-items-center rounded-full ring-1 ring-marble-50/20 transition hover:bg-brass-400 hover:text-pine-950 hover:ring-brass-400" aria-label="{{ ucfirst($network) }}">
                                <x-icon :name="$network" class="size-4.5"/>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:col-span-8">
                <div>
                    <p class="mb-4 text-sm font-semibold text-marble-50">Experiences</p>
                    <ul class="space-y-2.5 text-sm">
                        @foreach ($experiences as $exp)
                            <li><a href="{{ route('packages.index', ['category' => $exp->slug]) }}" class="hover:text-brass-300">{{ $exp->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="mb-4 text-sm font-semibold text-marble-50">Company</p>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('packages.index') }}" class="hover:text-brass-300">All journeys</a></li>
                        <li><a href="{{ route('destinations.index') }}" class="hover:text-brass-300">Destinations</a></li>
                        <li><a href="{{ route('stays') }}" class="hover:text-brass-300">Where you’ll stay</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-brass-300">About us</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-brass-300">Contact</a></li>
                    </ul>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <p class="mb-4 text-sm font-semibold text-marble-50">Talk to us</p>
                    <ul class="space-y-3 text-sm">
                        @if (setting('contact_phone_ca'))<li class="flex gap-2.5"><x-icon name="phone" class="mt-0.5 size-4 shrink-0 text-brass-400"/><span>Canada {{ setting('contact_phone_ca') }}</span></li>@endif
                        @if (setting('contact_phone'))<li class="flex gap-2.5"><x-icon name="phone" class="mt-0.5 size-4 shrink-0 text-brass-400"/><span>Pakistan {{ setting('contact_phone') }}</span></li>@endif
                        @if (setting('contact_email'))<li class="flex gap-2.5"><x-icon name="mail" class="mt-0.5 size-4 shrink-0 text-brass-400"/><a href="mailto:{{ setting('contact_email') }}" class="break-all hover:text-brass-300">{{ setting('contact_email') }}</a></li>@endif
                        @if (setting('contact_address'))<li class="flex gap-2.5"><x-icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-brass-400"/><span>{!! nl2br(e(setting('contact_address'))) !!}</span></li>@endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="mt-14 flex flex-col gap-3 border-t border-marble-50/10 pt-6 text-xs text-marble-50/50 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
            <p>Prices in USD per person, twin sharing. CAD amounts are approximate.</p>
        </div>
    </div>
</footer>

@if ($whatsapp)
    <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank" rel="noopener"
       class="wa-float fixed right-4 bottom-4 z-40 inline-flex items-center gap-2 rounded-full bg-[#1f8a4c] px-4 py-3 text-sm font-semibold text-white shadow-xl shadow-pine-950/25 transition hover:-translate-y-0.5 sm:right-6 sm:bottom-6">
        <x-icon name="chat" class="size-5"/><span class="hidden sm:inline">WhatsApp us</span>
    </a>
@endif
</body>
</html>
