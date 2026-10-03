@props(['eyebrow' => null, 'title', 'intro' => null, 'light' => false, 'center' => false])
<div {{ $attributes->class(['max-w-2xl', 'mx-auto text-center' => $center]) }} data-reveal>
    @if ($eyebrow)
        <p @class(['mb-2 text-sm font-semibold', $light ? 'text-lake-300' : 'text-brand-700'])>{{ $eyebrow }}</p>
    @endif
    <h2 @class(['heading-lg text-balance', $light ? 'text-white' : 'text-slate-900'])>{{ $title }}</h2>
    @if ($intro)
        <p @class(['mt-3 text-base leading-relaxed sm:text-lg', $light ? 'text-white/75' : 'text-slate-600'])>{{ $intro }}</p>
    @endif
</div>
