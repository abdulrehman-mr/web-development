<x-admin-layout :title="'Booking '.$booking->reference">
    <x-slot:actions><x-admin.delete :action="route('admin.bookings.destroy', $booking)" label="Delete booking"/></x-slot:actions>
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="min-w-0 space-y-6 xl:col-span-2">
            <x-admin.card title="Guest">
                <dl class="grid gap-4 text-sm sm:grid-cols-2">
                    @foreach ([
                        'Name' => $booking->full_name,
                        'Email' => $booking->email,
                        'Phone / WhatsApp' => $booking->phone ?: '—',
                        'Travelling from' => $booking->country ?: '—',
                    ] as $label => $value)
                        <div><dt class="text-xs text-ink/50">{{ $label }}</dt><dd class="mt-0.5 font-medium break-words">{{ $value }}</dd></div>
                    @endforeach
                </dl>
                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="mailto:{{ $booking->email }}?subject={{ rawurlencode('Your journey: '.$booking->package?->title.' ('.$booking->reference.')') }}" class="btn btn-ghost py-2"><x-icon name="mail" class="size-4"/>Email guest</a>
                    @if ($booking->phone)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $booking->phone) }}" target="_blank" rel="noopener" class="btn btn-ghost py-2"><x-icon name="chat" class="size-4"/>WhatsApp</a>
                    @endif
                </div>
            </x-admin.card>

            <x-admin.card title="Trip">
                <dl class="grid gap-4 text-sm sm:grid-cols-3">
                    <div class="sm:col-span-3"><dt class="text-xs text-ink/50">Journey</dt><dd class="mt-0.5 font-medium">@if($booking->package)<a href="{{ route('admin.packages.edit', $booking->package) }}" class="text-brand-800 hover:underline">{{ $booking->package->title }}</a>@else — @endif</dd></div>
                    <div><dt class="text-xs text-ink/50">Start date</dt><dd class="mt-0.5 font-medium">{{ $booking->start_date?->format('l, j F Y') }}</dd></div>
                    <div><dt class="text-xs text-ink/50">Travellers</dt><dd class="mt-0.5 font-medium">{{ $booking->adults }} adults, {{ $booking->children }} children</dd></div>
                    <div><dt class="text-xs text-ink/50">Rooms</dt><dd class="mt-0.5 font-medium">{{ $booking->room_preference ?: '—' }}</dd></div>
                </dl>
                @if ($booking->special_requests)
                    <div class="mt-5 rounded-xl bg-white p-4 text-sm"><p class="mb-1 text-xs text-ink/50">Special requests</p><p class="whitespace-pre-line">{{ $booking->special_requests }}</p></div>
                @endif
            </x-admin.card>
        </div>

        <form method="post" action="{{ route('admin.bookings.update', $booking) }}">
            @csrf @method('PUT')
            <x-admin.card title="Manage">
                <div class="space-y-4">
                    <div>
                        <span class="label">Status</span>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (\App\Models\Booking::STATUSES as $s)
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="{{ $s }}" @checked($booking->status === $s) class="peer sr-only">
                                    <span class="block rounded-xl px-3 py-2 text-center text-sm capitalize ring-1 ring-slate-300 peer-checked:bg-brand-800 peer-checked:text-white peer-checked:ring-brand-800">{{ $s }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <x-admin.input name="total_estimate" type="number" step="1" label="Quoted total (USD)" :value="$booking->total_estimate"/>
                    <x-admin.textarea name="admin_notes" label="Internal notes" :value="$booking->admin_notes" rows="5" help="Only visible to your team."/>
                    <button class="btn btn-primary w-full">Update booking</button>
                    <p class="text-center text-xs text-ink/40">Received {{ $booking->created_at->format('j M Y, g:ia') }}</p>
                </div>
            </x-admin.card>
        </form>
    </div>
</x-admin-layout>
