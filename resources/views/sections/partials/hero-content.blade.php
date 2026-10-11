{{-- Hero text, buttons, trust line, search and selling points. --}}
@php
    $compact ??= false;
    $phonePhoto ??= false;
    $slideText ??= null;
    $alignment = ['left' => 'text-left', 'center' => 'mx-auto text-center', 'right' => 'ml-auto text-right'][$align];
    $row = ['left' => 'justify-start', 'center' => 'justify-center', 'right' => 'justify-end'][$align];
    $titleClass = ['medium' => 'heading-lg', 'large' => 'heading-xl', 'xlarge' => 'heading-2xl'][$s['title_size']];
    // Mosaic and cards put the text in a narrower column.
    if (in_array($layout, ['mosaic', 'cards'], true) && $s['title_size'] === 'large') {
        $titleClass = 'heading-xl lg:text-[3.5rem]';
    }
    $firstSlide = $slideText[0] ?? [];
@endphp
<div @class(['w-full min-w-0', $alignment, $s['search'] && $s['search_style'] === 'bar' ? 'max-w-4xl' : 'max-w-3xl'])
     @if ($slideText) x-data="{ text: @js($slideText), fallback: @js(['title' => $s['title'], 'subtitle' => $s['subtitle']]) }" @endif>
    <div @class([
        'flex min-h-[31rem] flex-col justify-end pt-10 sm:min-h-[34rem] lg:block lg:min-h-0 lg:p-0' => $phonePhoto,
        'pb-24' => $phonePhoto && $s['search'],
        'pb-10' => $phonePhoto && ! $s['search'],
    ])>
        @if ($s['eyebrow'])
            <p @class(['mb-5 inline-flex w-fit max-w-full items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium ring-1', $tone['eyebrow'], 'lg:mb-6' => ! $compact])>
                <x-icon name="sparkle" class="size-4 shrink-0"/><span>{{ $s['eyebrow'] }}</span>
            </p>
        @endif

        <{{ $titleTag }} @class([$titleClass, 'text-balance', $tone['title']]) @if ($slideText) x-text="text[active]?.title || fallback.title" @endif>{{ ($firstSlide['title'] ?? '') ?: $s['title'] }}</{{ $titleTag }}>

        @if ($s['subtitle'] || $slideText)
            <p @class(['mt-4 max-w-2xl text-base leading-relaxed sm:text-lg', $tone['sub'], 'lg:mt-6' => ! $compact, 'mx-auto' => $align === 'center', 'ml-auto' => $align === 'right'])
               @if ($slideText) x-text="text[active]?.subtitle || fallback.subtitle" @endif>{{ ($firstSlide['subtitle'] ?? '') ?: $s['subtitle'] }}</p>
        @endif

        @if ($s['primary_label'] || $s['secondary_label'])
            <div class="mt-8 flex flex-wrap gap-3 {{ $row }}">
                @if ($s['primary_label'])
                    <a href="{{ $s['primary_url'] ?: '#' }}" class="btn {{ $tone['btn1'] }} py-3.5 pr-3.5">{{ $s['primary_label'] }}<span class="grid size-7 place-items-center rounded-full bg-current/10"><x-icon name="arrow-right" class="size-4"/></span></a>
                @endif
                @if ($s['secondary_label'])
                    <a href="{{ $s['secondary_url'] ?: '#' }}" class="btn {{ $tone['btn2'] }} py-3.5">{{ $s['secondary_label'] }}</a>
                @endif
            </div>
        @endif

        @if ($s['proof_text'])
            <p class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm {{ $row }} {{ $tone['muted'] }}">
                <span class="flex gap-0.5 text-amber-400" aria-hidden="true">@for ($i = 0; $i < 5; $i++)<x-icon name="star" class="size-4"/>@endfor</span>
                <span class="font-medium">{{ $s['proof_text'] }}</span>
            </p>
        @endif
    </div>

    @if ($s['search'])
        @include('sections.partials.hero-search')
    @endif

    @if ($s['badges'])
        @php $badgeTone = $phonePhoto ? ['muted' => 'text-slate-600', 'icon' => 'text-brand-600'] : $tone; @endphp
        <ul class="mt-6 flex flex-wrap gap-x-6 gap-y-3 text-sm lg:mt-8 {{ $row }} {{ $badgeTone['muted'] }}">
            @foreach ($s['badges'] as $badge)
                <li class="flex items-center gap-2"><x-icon :name="$badge['icon']" class="size-4.5 shrink-0 {{ $badgeTone['icon'] }}"/>{{ $badge['text'] }}</li>
            @endforeach
        </ul>
    @endif
</div>
