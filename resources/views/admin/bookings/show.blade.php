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
                    <div><dt class="text-xs text-ink/50">Start date</dt><dd class="mt-0.5 font-medium">{{ $booking->start_date?->format('l, j F Y') }}@if ($booking->departure)<span class="ml-2 rounded-full bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-800">Group departure{{ $booking->departure->label ? ': '.$booking->departure->label : '' }}</span>@endif</dd></div>
                    <div><dt class="text-xs text-ink/50">Travellers</dt><dd class="mt-0.5 font-medium">{{ $booking->adults }} adults, {{ $booking->children }} children</dd></div>
                    <div><dt class="text-xs text-ink/50">Room</dt><dd class="mt-0.5 font-medium">{{ $booking->room_preference ?: '—' }}</dd></div>
                </dl>
                @if (! empty($booking->options))
                    <dl class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                        @foreach ($booking->options as $choice)
                            <div><dt class="text-xs text-ink/50">{{ $choice['group'] }}</dt><dd class="mt-0.5 font-medium">{{ $choice['label'] }}</dd></div>
                        @endforeach
                    </dl>
                @endif
                @if (! empty($booking->extras))
                    <div class="mt-4 text-sm"><p class="text-xs text-ink/50">Extras</p>
                        <ul class="mt-1 list-inside list-disc">@foreach ($booking->extras as $extra)<li>{{ $extra['name'] }} <span class="text-slate-500">({{ money_base($extra['price']) }} {{ $extra['per'] === 'person' ? 'per person' : 'per booking' }})</span></li>@endforeach</ul>
                    </div>
                @endif
                @if (! empty($booking->price_breakdown))
                    <dl class="mt-4 space-y-1.5 rounded-xl bg-slate-50 p-4 text-sm">
                        @foreach ($booking->price_breakdown as $line)
                            <div class="flex justify-between gap-4"><dt class="text-slate-600">{{ $line['label'] }}</dt><dd class="font-medium">{{ money_base($line['amount']) }}</dd></div>
                        @endforeach
                        <div class="flex justify-between gap-4 border-t border-slate-200 pt-1.5 font-semibold"><dt>Quoted total</dt><dd>{{ money_base($booking->total_estimate) }}</dd></div>
                    </dl>
                @endif
                @if ($booking->special_requests)
                    <div class="mt-5 rounded-xl bg-white p-4 text-sm"><p class="mb-1 text-xs text-ink/50">Special requests</p><p class="whitespace-pre-line">{{ $booking->special_requests }}</p></div>
                @endif
            </x-admin.card>
            <x-admin.card title="Payments" x-data="{ copied: false }">
                <dl class="grid grid-cols-3 gap-3 text-sm">
                    <div class="rounded-xl bg-slate-50 p-3"><dt class="text-xs text-slate-500">Total ({{ currencies()->base()->code }})</dt><dd class="mt-1 font-semibold">{{ money_base($booking->total_estimate) }}</dd></div>
                    <div class="rounded-xl bg-slate-50 p-3"><dt class="text-xs text-slate-500">Paid</dt><dd class="mt-1 font-semibold text-brand-700">{{ money_base($booking->amount_paid) }}</dd></div>
                    <div class="rounded-xl bg-slate-50 p-3"><dt class="text-xs text-slate-500">Balance</dt><dd class="mt-1 font-semibold">{{ money_base($booking->balanceDue()) }}</dd></div>
                </dl>
                @if ($booking->currency && $booking->currency !== currencies()->base()->code)
                    <p class="mt-3 text-xs text-slate-500">Guest was browsing in {{ $booking->currency }}: total ≈ {{ format_money($booking->total_estimate * $booking->exchange_rate, $booking->currency) }} at the rate on the booking date.</p>
                @endif
                @if ($booking->payments->isNotEmpty())
                    <div class="-mx-5 mt-4 overflow-x-auto sm:-mx-6">
                        <table class="w-full min-w-[32rem] text-left text-sm">
                            <thead class="text-xs text-slate-500"><tr><th class="px-5 py-2 font-medium sm:px-6">Date</th><th class="py-2 font-medium">Method</th><th class="py-2 font-medium">Amount</th><th class="py-2 font-medium">Status</th><th class="py-2 pr-5 font-medium">Provider ref.</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($booking->payments as $p)
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">{{ $p->created_at->format('j M Y, H:i') }}</td>
                                        <td class="py-2">{{ app(\App\Support\Payments\PaymentGateways::class)->find($p->gateway)?->name() ?? $p->gateway }} · {{ $p->type }}</td>
                                        <td class="py-2 font-medium">{{ $p->formattedAmount() }}</td>
                                        <td class="py-2"><span @class(['rounded-md px-2 py-0.5 text-xs font-semibold capitalize', 'bg-brand-100 text-brand-800' => $p->status === 'paid', 'bg-slate-100 text-slate-600' => in_array($p->status, ['pending', 'cancelled']), 'bg-rose-100 text-rose-700' => $p->status === 'failed'])>{{ $p->status }}</span>@if ($p->failure_reason)<span class="block text-xs text-slate-400">{{ Str::limit($p->failure_reason, 60) }}</span>@endif</td>
                                        <td class="py-2 pr-5 font-mono text-xs text-slate-500">{{ $p->gateway_reference ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                @php $link = \App\Http\Controllers\PaymentController::payLink($booking); @endphp
                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="button" class="btn btn-ghost py-2" @click="navigator.clipboard.writeText(@js($link)); copied = true; setTimeout(() => copied = false, 1500)"><x-icon name="external" class="size-4"/><span x-text="copied ? 'Link copied' : 'Copy payment link'">Copy payment link</span></button>
                    <form method="post" action="{{ route('admin.bookings.payment-link', $booking) }}">@csrf<button class="btn btn-ghost py-2"><x-icon name="mail" class="size-4"/>Email payment link to guest</button></form>
                </div>
                <p class="mt-2 text-xs text-slate-500">The link is valid for 30 days.</p>
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
                    <x-admin.input name="total_estimate" type="number" step="1" :label="'Quoted total ('.currencies()->base()->code.')'" :value="$booking->total_estimate"/>
                    <x-admin.textarea name="admin_notes" label="Internal notes" :value="$booking->admin_notes" rows="5" help="Only visible to your team."/>
                    <button class="btn btn-primary w-full">Update booking</button>
                    <p class="text-center text-xs text-ink/40">Received {{ $booking->created_at->format('j M Y, g:ia') }}</p>
                </div>
            </x-admin.card>
        </form>
    </div>
</x-admin-layout>
