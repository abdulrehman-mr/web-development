@php
    $dark = $s['background'] === 'dark';
    $masks = ['none' => '', 'rounded' => 'rounded-3xl', 'arch' => 'arch', 'circle' => 'rounded-full', 'blob' => 'mask-blob', 'leaf' => 'rounded-tl-[6rem] rounded-br-[6rem] rounded-tr-2xl rounded-bl-2xl', 'tilt' => '-rotate-2 rounded-3xl ring-8 ring-white shadow-2xl'];
    $shape = in_array($s['image_mask'], ['arch', 'circle'], true) ? ($s['image_mask'] === 'circle' ? 'aspect-square' : 'aspect-[4/5]') : 'aspect-[4/3]';
@endphp
<x-builder.section :section="$section" :background="$s['background']">
    <div class="container-x grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
        <x-media :src="media_url($s['image'])" alt="" @class([$masks[$s['image_mask']], $shape, 'lg:order-2' => $s['image_side'] === 'right']) data-reveal/>
        <div data-reveal>
            @if ($s['eyebrow'])<p @class(['mb-3 text-sm font-semibold', $dark ? 'text-lake-300' : 'text-brand-700'])>{{ $s['eyebrow'] }}</p>@endif
            <h2 @class(['heading-lg text-balance', $dark ? 'text-white' : 'text-slate-900'])>{{ $s['title'] }}</h2>
            @foreach (preg_split('/\n\s*\n/', trim($s['body'])) as $paragraph)
                @if ($paragraph !== '')<p @class(['mt-5 text-lg leading-relaxed', $dark ? 'text-white/75' : 'text-slate-600'])>{!! nl2br(e($paragraph)) !!}</p>@endif
            @endforeach
            @if ($s['stats'])
                <dl class="mt-10 grid grid-cols-3 gap-6">
                    @foreach ($s['stats'] as $stat)
                        <div @class(['border-t-2 pt-4', $dark ? 'border-lake-300' : 'border-brand-600'])>
                            <dd @class(['text-2xl font-extrabold tracking-tight sm:text-4xl', $dark ? 'text-white' : 'text-slate-900'])>{{ $stat['value'] }}</dd>
                            <dt @class(['mt-1 text-xs leading-snug sm:text-sm', $dark ? 'text-white/60' : 'text-slate-500'])>{{ $stat['label'] }}</dt>
                        </div>
                    @endforeach
                </dl>
            @endif
            @if ($s['button_label'])
                <a href="{{ $s['button_url'] ?: '#' }}" @class(['btn mt-9', $dark ? 'btn-light' : 'btn-primary'])>{{ $s['button_label'] }}</a>
            @endif
        </div>
    </div>
</x-builder.section>
