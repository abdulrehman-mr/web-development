{{-- Section heading with an optional "see all" link beside it. --}}
@props(['s', 'dark' => false, 'href' => null, 'center' => false])
@if ($s['title'] || ($s['intro'] ?? null) || ($s['eyebrow'] ?? null))
    <div @class(['flex flex-col justify-between gap-4 sm:flex-row sm:items-end' => ! $center, 'mb-10' => true])>
        <x-section-heading :eyebrow="$s['eyebrow'] ?? null" :title="$s['title']" :intro="$s['intro'] ?? null" :light="$dark" :center="$center"/>
        @if ($href && ($s['link_label'] ?? null))
            <a href="{{ $href }}" @class(['shrink-0 text-sm font-semibold hover:underline', $dark ? 'text-lake-300' : 'text-brand-700 hover:text-brand-800'])>{{ $s['link_label'] }}</a>
        @endif
    </div>
@endif
