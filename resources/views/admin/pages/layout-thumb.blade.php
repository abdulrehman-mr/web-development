{{-- Tiny diagram of a hero layout for the layout picker. --}}
@php $photo = 'bg-gradient-to-br from-brand-700 via-brand-500 to-lake-400'; @endphp
<span class="relative block aspect-[16/10] overflow-hidden rounded-xl bg-white ring-1 ring-slate-200">
    @switch($layout)
        @case('background')
            <span class="absolute inset-0 {{ $photo }}"></span>
            <span class="absolute top-1/2 left-[10%] w-1/2 -translate-y-1/2 space-y-1.5"><span class="block h-2 w-full rounded bg-white"></span><span class="block h-2 w-3/4 rounded bg-white"></span><span class="block h-1 w-2/3 rounded bg-white/70"></span><span class="mt-2 block h-2.5 w-1/3 rounded-full bg-white/90"></span></span>
            @break
        @case('split')
            <span class="absolute top-1/2 left-[8%] w-[42%] -translate-y-1/2 space-y-1.5"><span class="block h-2 w-full rounded bg-slate-800"></span><span class="block h-2 w-3/4 rounded bg-slate-800"></span><span class="block h-1 w-full rounded bg-slate-300"></span><span class="mt-2 block h-3 w-full rounded bg-slate-100 ring-1 ring-slate-200"></span></span>
            <span class="arch absolute top-[10%] right-[8%] bottom-[10%] w-[34%] {{ $photo }}"></span>
            @break
        @case('slider')
            <span class="absolute inset-0 {{ $photo }}"></span>
            <span class="absolute inset-x-0 top-[38%] mx-auto w-1/2 space-y-1.5"><span class="block h-2 w-full rounded bg-white"></span><span class="mx-auto block h-1 w-2/3 rounded bg-white/70"></span></span>
            <span class="absolute inset-x-0 bottom-[10%] flex justify-center gap-1"><span class="h-1 w-3 rounded-full bg-white"></span><span class="size-1 rounded-full bg-white/60"></span><span class="size-1 rounded-full bg-white/60"></span></span>
            <span class="absolute top-1/2 left-1.5 size-2.5 -translate-y-1/2 rounded-full bg-white/40"></span><span class="absolute top-1/2 right-1.5 size-2.5 -translate-y-1/2 rounded-full bg-white/40"></span>
            @break
        @case('mosaic')
            <span class="absolute top-1/2 left-[7%] w-[34%] -translate-y-1/2 space-y-1.5"><span class="block h-2 w-full rounded bg-slate-800"></span><span class="block h-2 w-2/3 rounded bg-slate-800"></span><span class="mt-2 block h-2.5 w-1/2 rounded-full bg-brand-600"></span></span>
            <span class="absolute inset-y-0 right-[5%] grid w-[48%] grid-cols-3 gap-1">
                @foreach ([[0, 'mt-2'], [1, '-mt-1'], [2, 'mt-2']] as [$c, $offset])
                    <span class="{{ $offset }} flex flex-col gap-1"><span class="h-6 rounded-md {{ $photo }}"></span><span class="h-8 rounded-md {{ $photo }} opacity-80"></span><span class="h-6 rounded-md {{ $photo }} opacity-60"></span></span>
                @endforeach
            </span>
            @break
        @case('cards')
            <span class="absolute inset-0 bg-gradient-to-br from-slate-800 via-brand-900 to-brand-700"></span>
            <span class="absolute top-1/2 left-[7%] w-[34%] -translate-y-1/2 space-y-1.5"><span class="block h-2.5 w-full rounded bg-white"></span><span class="block h-1 w-full rounded bg-white/60"></span><span class="mt-2 block h-2.5 w-1/2 rounded bg-white/90"></span></span>
            <span class="absolute top-[22%] right-0 bottom-[22%] flex w-[52%] gap-1.5"><span class="w-[38%] rounded-md bg-gradient-to-b from-lake-300 to-brand-500 ring-1 ring-white/50"></span><span class="w-[38%] rounded-md bg-gradient-to-b from-lake-200 to-lake-500 ring-1 ring-white/50"></span><span class="w-[24%] rounded-l-md bg-gradient-to-b from-brand-300 to-brand-600"></span></span>
            @break
        @default
            <span class="absolute inset-x-[6%] top-[8%] h-[55%] rounded-lg {{ $photo }}"></span>
            <span class="absolute top-[38%] left-[12%] w-1/2 space-y-1"><span class="block h-2 w-full rounded bg-white"></span><span class="block h-1 w-2/3 rounded bg-white/70"></span></span>
            <span class="absolute inset-x-[6%] top-[72%] space-y-1"><span class="block h-1 w-full rounded bg-slate-200"></span><span class="block h-1 w-4/5 rounded bg-slate-200"></span><span class="block h-1 w-3/5 rounded bg-slate-200"></span></span>
    @endswitch
</span>
