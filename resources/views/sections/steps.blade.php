@php $dark = $s['background'] === 'dark'; @endphp
@if ($s['items'])
<x-builder.section :section="$section" :background="$s['background']">
    <div class="container-x">
        <x-builder.heading :s="$s" :dark="$dark" center/>
        @php $cols = [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3'][count($s['items'])] ?? 'lg:grid-cols-4'; @endphp
        <ol class="relative grid gap-10 sm:grid-cols-2 {{ $cols }} lg:gap-8 lg:before:absolute lg:before:top-5 lg:before:right-[12%] lg:before:left-[12%] lg:before:h-px {{ $dark ? 'lg:before:bg-white/15' : 'lg:before:bg-slate-200' }}">
            @foreach ($s['items'] as $i => $item)
                <li class="relative lg:text-center" data-reveal>
                    <span @class(['relative grid size-10 place-items-center rounded-full bg-brand-700 text-sm font-bold text-white ring-8 lg:mx-auto', ['white' => 'ring-white', 'light' => 'ring-slate-50', 'tint' => 'ring-brand-50', 'dark' => 'ring-brand-950'][$s['background']]])>{{ $i + 1 }}</span>
                    <h3 @class(['mt-4 font-semibold', $dark ? 'text-white' : 'text-slate-900'])>{{ $item['title'] }}</h3>
                    <p @class(['mt-1.5 text-sm leading-relaxed', $dark ? 'text-white/65' : 'text-slate-600'])>{{ $item['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</x-builder.section>
@endif
