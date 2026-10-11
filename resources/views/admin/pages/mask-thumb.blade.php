{{-- Tiny preview of a photo shape. --}}
@php
    $shapes = ['none' => '', 'rounded' => 'rounded-[3px]', 'arch' => 'arch', 'circle' => 'rounded-full', 'blob' => 'mask-blob', 'leaf' => 'rounded-tl-[6px] rounded-br-[6px]', 'tilt' => 'rotate-6 rounded-[2px]'];
@endphp
<span class="inline-block size-4 shrink-0 bg-current opacity-80 {{ $shapes[$mask] ?? '' }}" aria-hidden="true"></span>
