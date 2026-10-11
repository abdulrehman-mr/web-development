@props(['title' => null, 'description' => null, 'overlay' => false])
@php
    $siteName = setting('site_name', 'Safarnama');
    $nav = app(\App\Support\PageBuilder::class)->menu();
    $experiencesTitle = setting('menu_experiences');
    $experiences = \App\Models\Category::orderBy('sort_order')->get(['name', 'slug', 'tagline']);
    $headerButton = setting('header_button_label');
    $headerButtonUrl = url(setting('header_button_url', '/contact'));
    $whatsapp = setting('contact_whatsapp');
    $switchable = setting('currency_switcher', '1') === '1' ? currencies()->enabled() : collect();
    $currentCurrency = currencies()->current();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · '.$siteName : $siteName.' — '.setting('site_tagline') }}</title>
    <meta name="description" content="{{ $description ?? setting('site_description') }}">
    <meta property="og:title" content="{{ $title ?? $siteName }}">
    <meta property="og:description" content="{{ $description ?? setting('site_description', setting('site_tagline')) }}">
    <meta name="theme-color" content="{{ \App\Support\Theme::colors()['brand'] }}">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%2301411c'/%3E%3Cpath d='M16 5c1 3 4 5 7 6.5V27H9V11.5C12 10 15 8 16 5Z' fill='%23ffffff'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if ($themeCss = \App\Support\Theme::css())<style>{!! $themeCss !!}</style>@endif
</head>
<body class="min-h-screen">
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:rounded-full focus:bg-lake-300 focus:px-4 focus:py-2 focus:text-slate-900">Skip to content</a>

{{-- Header --}}
<header x-data="{ open: false, scrolled: false, exp: false, overlay: @js($overlay) }"
        x-init="scrolled = window.scrollY > 8"
        @scroll.window="scrolled = window.scrollY > 8"
        @keydown.escape.window="open = false; exp = false"
        :class="{ 'shadow-[0_1px_0_rgb(15_23_42/0.08),0_8px_24px_-12px_rgb(15_23_42/0.18)]': scrolled || open, 'shadow-[0_1px_0_rgb(15_23_42/0.06)]': !scrolled && !open, 'is-clear': overlay && !scrolled && !open && !exp }"
        @class(['site-header fixed inset-x-0 top-0 z-50 bg-white/95 backdrop-blur transition-[background-color,box-shadow] duration-300', 'is-clear' => $overlay])>
    <div class="container-x flex h-16 items-center justify-between gap-6 lg:h-[4.5rem]">
        <a href="{{ route('home') }}" class="hdr-logo flex items-center gap-2 text-slate-900" aria-label="{{ $siteName }} home">
            @if (setting('logo'))
                <img src="{{ media_url(setting('logo')) }}" alt="{{ $siteName }}" class="h-9 w-auto">
            @else
                <svg viewBox="0 0 32 40" class="h-8 w-6 text-brand-700" aria-hidden="true"><path d="M16 1c1.3 4 5.3 7.3 10 9.5C29 12 31 15 31 18.5V39H1V18.5C1 15 3 12 6 10.5 10.7 8.3 14.7 5 16 1Z" fill="currentColor"/><path d="M16 12c.7 2.1 2.9 3.9 5.3 5.1 1.5.8 2.4 2.2 2.4 3.8V39H8.3V20.9c0-1.6.9-3 2.4-3.8 2.4-1.2 4.6-3 5.3-5.1Z" fill="#fff" opacity=".9"/></svg>
                <span class="text-xl font-extrabold tracking-tight">{{ $siteName }}</span>
            @endif
        </a>

        <nav class="hidden items-center gap-0.5 lg:flex" aria-label="Main">
            @if ($experiencesTitle && $experiences->isNotEmpty())
            <div class="relative" @mouseenter="exp = true" @mouseleave="exp = false">
                <button type="button" @click="exp = !exp" :aria-expanded="exp" class="hdr-link inline-flex items-center gap-1 rounded-lg px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-slate-900">
                    {{ $experiencesTitle }} <x-icon name="chevron-down" class="size-4 transition" ::class="exp && 'rotate-180'"/>
                </button>
                <div x-cloak x-show="exp" x-transition.opacity.duration.150ms class="absolute top-full left-0 w-[36rem] pt-2">
                    <div class="grid grid-cols-2 gap-1 rounded-2xl bg-white p-2 shadow-xl ring-1 ring-slate-200">
                        @foreach ($experiences as $exp)
                            <a href="{{ route('packages.index', ['category' => $exp->slug]) }}" class="rounded-xl p-3 transition hover:bg-slate-50">
                                <span class="block text-sm font-semibold text-slate-900">{{ $exp->name }}</span>
                                <span class="mt-0.5 block text-xs leading-snug text-slate-500">{{ $exp->tagline }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            @foreach ($nav as $link)
                <a href="{{ $link['url'] }}" @class(['hdr-link rounded-lg px-3.5 py-2 text-sm font-medium transition', $link['active'] ? 'bg-brand-50 text-brand-800' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'])>{{ $link['label'] }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            @if ($switchable->count() > 1)
                <div class="relative hidden sm:block" x-data="{ cur: false }" @click.outside="cur = false" @keydown.escape="cur = false">
                    <button type="button" @click="cur = !cur" :aria-expanded="cur" class="hdr-ring inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50" aria-label="Change currency">
                        <x-icon name="globe" class="size-4 text-slate-500"/>{{ $currentCurrency->code }}<x-icon name="chevron-down" class="size-3.5 text-slate-400"/>
                    </button>
                    <form x-cloak x-show="cur" x-transition.opacity.duration.100ms method="post" action="{{ route('currency.update') }}" class="absolute top-full right-0 mt-2 max-h-80 w-64 overflow-y-auto rounded-xl bg-white p-1.5 shadow-xl ring-1 ring-slate-200">
                        @csrf
                        @foreach ($switchable as $c)
                            <button name="currency" value="{{ $c->code }}" @class(['flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left text-sm transition', $c->code === $currentCurrency->code ? 'bg-brand-50 font-semibold text-brand-800' : 'text-slate-700 hover:bg-slate-50'])>
                                <span>{{ $c->name }}</span><span class="font-mono text-xs text-slate-500">{{ $c->symbol }} {{ $c->code }}</span>
                            </button>
                        @endforeach
                    </form>
                </div>
            @endif
            @if (setting('contact_phone_ca'))
                <a href="tel:{{ preg_replace('/[^\d+]/', '', setting('contact_phone_ca')) }}" class="hdr-link hidden text-sm font-medium text-slate-600 hover:text-slate-900 xl:inline">{{ setting('contact_phone_ca') }}</a>
            @endif
            @if ($headerButton)
                <a href="{{ $headerButtonUrl }}" class="hdr-cta btn btn-primary hidden py-2.5 sm:inline-flex">{{ $headerButton }}</a>
            @endif
            <button type="button" @click="open = !open" class="hdr-ring grid size-10 place-items-center rounded-lg text-slate-800 ring-1 ring-slate-300 lg:hidden" :aria-expanded="open" aria-controls="mobile-nav">
                <span class="sr-only">Menu</span>
                <x-icon name="menu" x-show="!open"/>
                <x-icon name="x" x-show="open" x-cloak/>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div id="mobile-nav" x-cloak x-show="open" x-collapse class="border-t border-slate-200 lg:hidden">
        <nav class="container-x max-h-[calc(100dvh-4rem)] overflow-y-auto py-4" aria-label="Mobile">
            @foreach ($nav as $link)
                <a href="{{ $link['url'] }}" class="block border-b border-slate-100 py-3.5 text-lg font-semibold text-slate-900">{{ $link['label'] }}</a>
            @endforeach
            @if ($experiencesTitle && $experiences->isNotEmpty())
                <p class="mt-6 mb-2 text-sm font-semibold text-slate-500">{{ $experiencesTitle }}</p>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($experiences as $exp)
                        <a href="{{ route('packages.index', ['category' => $exp->slug]) }}" class="rounded-xl bg-slate-50 p-3 text-sm font-medium text-slate-800">{{ $exp->name }}</a>
                    @endforeach
                </div>
            @endif
            @if ($switchable->count() > 1)
                <form method="post" action="{{ route('currency.update') }}" class="mt-6">
                    @csrf
                    <label for="m-currency" class="mb-2 block text-sm font-semibold text-slate-500">Currency</label>
                    <select id="m-currency" name="currency" onchange="this.form.submit()" class="field">
                        @foreach ($switchable as $c)
                            <option value="{{ $c->code }}" @selected($c->code === $currentCurrency->code)>{{ $c->code }} — {{ $c->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            @if ($headerButton)
                <a href="{{ $headerButtonUrl }}" class="btn btn-primary mt-6 w-full">{{ $headerButton }}</a>
            @endif
        </nav>
    </div>
</header>

<main id="main">
    {{ $slot }}
</main>

{{-- Footer --}}
<footer class="bg-brand-950 text-white/70">
    <div class="container-x py-14 sm:py-16">
        <div class="grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <p class="text-2xl font-extrabold tracking-tight text-white">{{ $siteName }}</p>
                <p class="mt-3 max-w-sm text-sm leading-relaxed">{{ setting('footer_about') }}</p>
                <div class="mt-6 flex gap-2">
                    @foreach (['instagram', 'facebook', 'youtube'] as $network)
                        @if (setting('social_'.$network))
                            <a href="{{ setting('social_'.$network) }}" target="_blank" rel="noopener" class="grid size-10 place-items-center rounded-lg bg-white/5 ring-1 ring-white/10 transition hover:bg-white hover:text-brand-900" aria-label="{{ ucfirst($network) }}">
                                <x-icon :name="$network" class="size-4.5"/>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:col-span-8">
                <div>
                    <p class="mb-4 text-sm font-semibold text-white">{{ $experiencesTitle ?: 'Experiences' }}</p>
                    <ul class="space-y-2.5 text-sm">
                        @foreach ($experiences as $exp)
                            <li><a href="{{ route('packages.index', ['category' => $exp->slug]) }}" class="hover:text-white">{{ $exp->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="mb-4 text-sm font-semibold text-white">{{ setting('footer_links_title') }}</p>
                    <ul class="space-y-2.5 text-sm">
                        @foreach ($nav as $link)
                            <li><a href="{{ $link['url'] }}" class="hover:text-white">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <p class="mb-4 text-sm font-semibold text-white">{{ setting('footer_contact_title') }}</p>
                    <ul class="space-y-3 text-sm">
                        @if (setting('contact_phone_ca'))<li class="flex gap-2.5"><x-icon name="phone" class="mt-0.5 size-4 shrink-0 text-lake-300"/><span>{{ setting('label_phone_secondary') }} {{ setting('contact_phone_ca') }}</span></li>@endif
                        @if (setting('contact_phone'))<li class="flex gap-2.5"><x-icon name="phone" class="mt-0.5 size-4 shrink-0 text-lake-300"/><span>{{ setting('label_phone_primary') }} {{ setting('contact_phone') }}</span></li>@endif
                        @if (setting('contact_email'))<li class="flex gap-2.5"><x-icon name="mail" class="mt-0.5 size-4 shrink-0 text-lake-300"/><a href="mailto:{{ setting('contact_email') }}" class="break-all hover:text-white">{{ setting('contact_email') }}</a></li>@endif
                        @if (setting('contact_address'))<li class="flex gap-2.5"><x-icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-lake-300"/><span>{!! nl2br(e(setting('contact_address'))) !!}</span></li>@endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="mt-12 flex flex-col gap-3 border-t border-white/10 pt-6 text-xs text-white/50 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
            <p>{{ setting('footer_note') }} Amounts converted from {{ currencies()->base()->code }} are approximate.</p>
        </div>
    </div>
</footer>

@if ($whatsapp)
    <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank" rel="noopener"
       class="wa-float fixed right-4 bottom-4 z-40 inline-flex items-center gap-2 rounded-full bg-[#25d366] text-slate-950 px-4 py-3 text-sm font-semibold shadow-xl shadow-brand-950/25 transition hover:-translate-y-0.5 sm:right-6 sm:bottom-6">
        <x-icon name="chat" class="size-5"/><span class="hidden sm:inline">{{ setting('whatsapp_label', 'WhatsApp us') }}</span>
    </a>
@endif
</body>
</html>
