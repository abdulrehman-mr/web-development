@php
    $dark = $s['background'] === 'dark';
    $center = $s['align'] === 'center';
    $hasStats = ! empty($s['stats']);
@endphp
<x-builder.section :section="$section" :background="$s['background']">
    <div @class(['container-x', 'grid gap-12 lg:grid-cols-12 lg:gap-20' => $hasStats && ! $center])>
        <div @class(['lg:col-span-7' => $hasStats && ! $center, 'mx-auto max-w-3xl text-center' => $center, 'max-w-3xl' => ! $center && ! $hasStats])>
            @if ($s['eyebrow'])<p @class(['mb-3 text-sm font-semibold', $dark ? 'text-lake-300' : 'text-brand-700'])>{{ $s['eyebrow'] }}</p>@endif
            @if ($s['title'])<h2 @class(['heading-lg mb-6 text-balance', $dark ? 'text-white' : 'text-slate-900'])>{{ $s['title'] }}</h2>@endif
            <div @class(['space-y-4 text-lg leading-relaxed', $dark ? 'text-white/75' : 'text-slate-600'])>
                @foreach (preg_split('/\n\s*\n/', trim($s['body'])) as $paragraph)
                    @if ($paragraph !== '')<p>{!! nl2br(e($paragraph)) !!}</p>@endif
                @endforeach
            </div>
        </div>
        @if ($hasStats)
            <dl @class(['grid grid-cols-3 gap-6 self-start', 'lg:col-span-5 lg:grid-cols-1 lg:gap-8' => ! $center, 'mx-auto mt-12 max-w-3xl text-center' => $center])>
                @foreach ($s['stats'] as $stat)
                    <div @class(['border-l-2 pl-4 lg:pl-6' => ! $center, $dark ? 'border-lake-300' : 'border-lake-300'])>
                        <dd @class(['text-3xl font-bold tracking-tight sm:text-5xl', $dark ? 'text-white' : 'text-slate-900'])>{{ $stat['value'] }}</dd>
                        <dt @class(['mt-1 text-xs sm:text-sm', $dark ? 'text-white/60' : 'text-ink/60'])>{{ $stat['label'] }}</dt>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>
</x-builder.section>
