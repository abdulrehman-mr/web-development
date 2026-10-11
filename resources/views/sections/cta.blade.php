@php
    $style = $s['style'];
    $onDark = $style !== 'light';
@endphp
<section id="section-{{ $section->id }}" class="px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
    <div @class(['relative isolate mx-auto max-w-7xl overflow-hidden rounded-3xl px-6 py-16 text-center sm:px-12 sm:py-24', 'text-white' => $onDark, 'bg-brand-800' => $style === 'brand', 'bg-brand-50 ring-1 ring-brand-100' => $style === 'light'])>
        @if ($style === 'image')
            <x-media :src="media_url($s['image'])" alt="" class="absolute! inset-0 -z-10"/>
            <div class="absolute inset-0 -z-10 bg-brand-950/75"></div>
        @endif
        <h2 @class(['heading-lg mx-auto max-w-3xl text-balance', 'text-slate-900' => ! $onDark])>{{ $s['title'] }}</h2>
        @if ($s['text'])<p @class(['mx-auto mt-4 max-w-xl', $onDark ? 'text-white/80' : 'text-slate-600'])>{{ $s['text'] }}</p>@endif
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            @if ($s['primary_label'])<a href="{{ $s['primary_url'] ?: route('contact') }}" @class(['btn', $onDark ? 'btn-light' : 'btn-primary'])>{{ $s['primary_label'] }}</a>@endif
            @if ($s['secondary_label'])<a href="{{ $s['secondary_url'] ?: route('packages.index') }}" @class(['btn', $onDark ? 'btn-ghost-light' : 'btn-ghost'])>{{ $s['secondary_label'] }}</a>@endif
        </div>
    </div>
</section>
