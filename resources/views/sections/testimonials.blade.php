@php $dark = $s['background'] === 'dark'; @endphp
@if ($testimonials->isNotEmpty())
<x-builder.section :section="$section" :background="$s['background']">
    <div class="container-x">
        <div class="mb-10 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <x-section-heading :eyebrow="$s['eyebrow']" :title="$s['title']" :light="$dark"/>
            @if ($s['rating_text'])
                <p @class(['flex items-center gap-2 text-sm', $dark ? 'text-white/75' : 'text-slate-600'])><x-icon name="star" class="size-4 text-amber-400"/>{{ $s['rating_text'] }}</p>
            @endif
        </div>
        <div class="grid gap-5 md:grid-cols-3">
            @foreach ($testimonials as $t)
                <figure class="flex flex-col rounded-2xl bg-white p-6 ring-1 ring-slate-200" data-reveal>
                    <div class="flex gap-0.5 text-amber-400" aria-label="{{ $t->rating }} out of 5">@for ($i = 0; $i < $t->rating; $i++)<x-icon name="star" class="size-4"/>@endfor</div>
                    <blockquote class="mt-4 flex-1 leading-relaxed text-slate-700">{{ $t->quote }}</blockquote>
                    <figcaption class="mt-6 border-t border-slate-100 pt-4 text-sm">
                        <span class="block font-semibold text-slate-900">{{ $t->name }}</span>
                        <span class="text-slate-500">{{ $t->location }}@if ($t->package) · {{ $t->package->title }}@endif</span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</x-builder.section>
@endif
