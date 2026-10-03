@props(['eyebrow' => null, 'title', 'intro' => null, 'light' => false, 'center' => false])
<div {{ $attributes->class(['max-w-2xl', 'mx-auto text-center' => $center]) }} data-reveal>
    @if ($eyebrow)
        <p @class(['eyebrow mb-3', 'text-brass-300' => $light])>{{ $eyebrow }}</p>
    @endif
    <h2 @class(['heading-lg', $light ? 'text-marble-50' : 'text-pine-950'])>{{ $title }}</h2>
    @if ($intro)
        <p @class(['mt-4 text-base leading-relaxed sm:text-lg', $light ? 'text-marble-50/70' : 'text-ink/65'])>{{ $intro }}</p>
    @endif
</div>
