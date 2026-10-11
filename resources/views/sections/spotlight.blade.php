@php $dark = $s['background'] === 'dark'; @endphp
@if ($package)
<x-builder.section :section="$section" :background="$s['background']">
    <div class="container-x grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
        <x-media :src="$package->hero_image_url" :alt="$package->title" @class(['aspect-[4/3] rounded-3xl lg:aspect-[5/4]', 'lg:order-2' => $s['image_side'] === 'right']) data-reveal/>
        <div data-reveal>
            @if ($s['eyebrow'])<p @class(['mb-3 text-sm font-semibold', $dark ? 'text-lake-300' : 'text-brand-700'])>{{ $s['eyebrow'] }}</p>@endif
            <h2 @class(['heading-lg text-balance', $dark ? 'text-white' : 'text-slate-900'])>{{ $package->title }}</h2>
            <p @class(['mt-4 text-lg leading-relaxed', $dark ? 'text-white/80' : 'text-slate-600'])>{{ $package->summary }}</p>
            <ul class="mt-7 space-y-3">
                @foreach (array_slice($package->highlights ?? [], 0, 5) as $highlight)
                    <li @class(['flex gap-3', $dark ? 'text-white/90' : 'text-slate-700'])><x-icon name="check" @class(['mt-0.5 size-5 shrink-0', $dark ? 'text-lake-300' : 'text-brand-600'])/>{{ $highlight }}</li>
                @endforeach
            </ul>
            <div class="mt-9 flex flex-wrap items-center gap-x-8 gap-y-5">
                <x-price :amount="$package->price" :original="$package->original_price" :light="$dark"/>
                <a href="{{ route('packages.show', $package) }}" @class(['btn', $dark ? 'btn-light' : 'btn-primary'])>{{ $s['button_label'] ?: 'See the journey' }}</a>
            </div>
        </div>
    </div>
</x-builder.section>
@endif
