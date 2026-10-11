@php $dark = $s['background'] === 'dark'; $center = $s['layout'] === 'center'; @endphp
@if ($faqs->isNotEmpty())
<x-builder.section :section="$section" :background="$s['background']">
    <div @class(['container-x', 'grid gap-10 lg:grid-cols-12' => ! $center, 'max-w-4xl' => $center])>
        <div @class(['lg:col-span-4' => ! $center, 'mb-10 text-center' => $center])>
            <x-section-heading :eyebrow="$s['eyebrow']" :title="$s['title']" :intro="$s['intro']" :light="$dark" :center="$center"/>
            @if ($s['button_label'])
                <a href="{{ $s['button_url'] ?: route('contact') }}" @class(['btn mt-6', $dark ? 'btn-ghost-light' : 'btn-ghost'])>{{ $s['button_label'] }}</a>
            @endif
        </div>
        <div @class(['lg:col-span-8' => ! $center, 'space-y-3' => $center, 'divide-y border-y' => ! $center, $dark ? 'divide-white/10 border-white/10' : 'divide-slate-200 border-slate-200']) x-data="{ open: {{ $center ? 'null' : '0' }} }">
            @foreach ($faqs as $faq)
                <div @class(['rounded-2xl bg-white ring-1 ring-slate-200' => $center])>
                    <button type="button" @click="open = open === {{ $loop->index }} ? null : {{ $loop->index }}" @class(['flex w-full items-center justify-between gap-6 text-left', $center ? 'p-5' : 'py-5']) :aria-expanded="open === {{ $loop->index }}">
                        <span @class(['font-semibold', $dark && ! $center ? 'text-white' : 'text-slate-900'])>{{ $faq->question }}</span>
                        <x-icon name="chevron-down" class="size-5 shrink-0 text-slate-400 transition" ::class="open === {{ $loop->index }} && 'rotate-180'"/>
                    </button>
                    <div x-show="open === {{ $loop->index }}" x-collapse @if ($center || ! $loop->first) x-cloak @endif>
                        <p @class(['max-w-2xl leading-relaxed', $center ? 'px-5 pb-5' : 'pb-5', $dark && ! $center ? 'text-white/70' : 'text-slate-600'])>{{ $faq->answer }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-builder.section>
@endif
