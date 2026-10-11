@php $dark = $s['background'] === 'dark'; @endphp
@if ($s['items'])
<x-builder.section :section="$section" :background="$s['background']">
    <div class="container-x">
        <x-builder.heading :s="$s" :dark="$dark"/>
        <div @class(['grid gap-px overflow-hidden rounded-3xl sm:grid-cols-2', $dark ? 'bg-white/10' : 'bg-slate-200', 'lg:grid-cols-3' => $s['columns'] === '3', 'lg:grid-cols-4' => $s['columns'] === '4'])>
            @foreach ($s['items'] as $item)
                <div @class(['p-8', $dark ? 'bg-brand-950' : 'bg-white'])>
                    <x-icon :name="$item['icon']" @class(['size-7', $dark ? 'text-lake-300' : 'text-brand-600'])/>
                    <h3 @class(['mt-5 text-lg font-semibold', $dark ? 'text-white' : 'text-slate-900'])>{{ $item['title'] }}</h3>
                    <p @class(['mt-2 text-sm leading-relaxed', $dark ? 'text-white/65' : 'text-slate-600'])>{{ $item['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-builder.section>
@endif
