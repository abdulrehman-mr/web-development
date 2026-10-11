{{-- Any page made of page-builder sections (Admin → Pages). --}}
<x-site-layout :title="$title ?? ($page->slug === 'home' ? null : $page->title)" :description="$description ?? $page->meta_description" :overlay="$overlay">
    <div @class(['pt-16 lg:pt-[4.5rem]' => ! $overlay])>
        @foreach ($blocks as $block)
            @include($block['view'], $block['data'])
        @endforeach

        @if (empty($blocks))
            <section class="container-x py-24 text-center">
                <h1 class="heading-lg text-slate-900">{{ $page->title }}</h1>
                @auth<p class="mt-4 text-slate-600">This page has no sections yet. <a href="{{ route('admin.pages.edit', $page) }}" class="font-semibold text-brand-700 underline">Add some in the admin</a>.</p>@endauth
            </section>
        @endif
    </div>
</x-site-layout>
