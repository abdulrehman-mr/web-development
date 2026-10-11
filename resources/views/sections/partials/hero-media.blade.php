{{-- Photo, uploaded video or YouTube/Vimeo loop behind the hero, plus the shade. --}}
<div class="absolute inset-0 -z-20 overflow-hidden" style="container-type:size">
    <x-media :src="media_url($s['image'])" alt="" :eager="$isFirst" class="absolute! inset-0"/>
    @if ($s['media_type'] === 'video' && $s['video'])
        <video class="absolute inset-0 size-full object-cover motion-reduce:hidden" autoplay muted loop playsinline preload="metadata" @if ($s['image']) poster="{{ media_url($s['image']) }}" @endif aria-hidden="true">
            <source src="{{ media_url($s['video']) }}" type="{{ str_ends_with(strtolower($s['video']), '.webm') ? 'video/webm' : 'video/mp4' }}">
        </video>
    @elseif ($embed)
        <iframe src="{{ $embed['src'] }}" title="Background video" tabindex="-1" aria-hidden="true" allow="autoplay; encrypted-media; picture-in-picture" referrerpolicy="strict-origin-when-cross-origin"
                class="pointer-events-none absolute top-1/2 left-1/2 h-[max(100cqh,56.25cqw)] w-[max(100cqw,177.78cqh)] -translate-x-1/2 -translate-y-1/2 border-0 motion-reduce:hidden"></iframe>
    @endif
</div>
@if ($shade)<div class="absolute inset-0 -z-10" style="{{ $shade }}"></div>@endif
