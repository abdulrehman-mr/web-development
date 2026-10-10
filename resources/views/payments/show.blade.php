@php
    $inBookingCurrency = fn ($base) => $booking->currency ? format_money($base * $booking->exchange_rate, $booking->currency) : money_base($base);
    $deposit = $booking->depositAmount();
    $balance = $booking->balanceDue();
    $canDeposit = $booking->amount_paid <= 0 && $deposit < $balance;
@endphp
<x-site-layout title="Pay for your journey">
    <section class="pt-28 pb-20 sm:pt-32">
        <div class="container-x grid max-w-5xl gap-8 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <h1 class="heading-lg text-slate-900">Secure your journey</h1>
                <p class="mt-2 text-slate-600">Booking <span class="font-mono font-semibold text-slate-900">{{ $booking->reference }}</span> · {{ $booking->package?->title }}</p>

                @if (session('status'))
                    <div class="mt-6 flex gap-3 rounded-2xl bg-brand-50 p-4 text-brand-800 ring-1 ring-brand-100" role="status"><x-icon name="check" class="mt-0.5 size-5 shrink-0"/>{{ session('status') }}</div>
                @endif
                @error('gateway')<div class="mt-6 rounded-2xl bg-rose-50 p-4 text-sm text-rose-700 ring-1 ring-rose-100" role="alert">{{ $message }}</div>@enderror

                @if ($balance <= 0)
                    <div class="mt-8 rounded-2xl bg-white p-8 text-center ring-1 ring-slate-200">
                        <span class="mx-auto grid size-14 place-items-center rounded-full bg-brand-50 text-brand-700"><x-icon name="check" class="size-7"/></span>
                        <p class="mt-4 text-xl font-bold text-slate-900">Paid in full</p>
                        <p class="mt-1 text-slate-600">Thank you! Your journey is fully paid.</p>
                    </div>
                @elseif ($gateways->isEmpty())
                    <div class="mt-8 rounded-2xl bg-slate-50 p-6 ring-1 ring-slate-200">
                        <p class="font-semibold text-slate-900">Online payment isn’t available yet</p>
                        <p class="mt-1 text-sm text-slate-600">Our team will email you payment instructions after confirming availability. Questions? Call {{ setting('contact_phone_ca') ?? setting('contact_phone') }}.</p>
                    </div>
                @else
                    <form method="post" action="{{ route('bookings.pay.start', $booking) }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" class="mt-8 space-y-8" x-data="{ type: '{{ $canDeposit ? 'deposit' : 'full' }}', gateway: '{{ $gateways->keys()->first() }}' }">
                        @csrf
                        <fieldset>
                            <legend class="mb-3 font-semibold text-slate-900">1. How much would you like to pay?</legend>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @if ($canDeposit)
                                    <label class="cursor-pointer rounded-2xl p-4 ring-1 transition" :class="type === 'deposit' ? 'bg-brand-50 ring-2 ring-brand-700' : 'bg-white ring-slate-200 hover:ring-slate-300'">
                                        <input type="radio" name="type" value="deposit" x-model="type" class="sr-only">
                                        <span class="block text-sm text-slate-600">Deposit ({{ (int) setting('deposit_percent', 25) }}%)</span>
                                        <span class="mt-1 block text-2xl font-bold tracking-tight text-slate-900">{{ $inBookingCurrency($deposit) }}</span>
                                        <span class="mt-1 block text-xs text-slate-500">Holds your dates. Balance due 30 days before travel.</span>
                                    </label>
                                @endif
                                <label class="cursor-pointer rounded-2xl p-4 ring-1 transition" :class="type === 'full' ? 'bg-brand-50 ring-2 ring-brand-700' : 'bg-white ring-slate-200 hover:ring-slate-300'">
                                    <input type="radio" name="type" value="full" x-model="type" class="sr-only">
                                    <span class="block text-sm text-slate-600">{{ $booking->amount_paid > 0 ? 'Remaining balance' : 'Pay in full' }}</span>
                                    <span class="mt-1 block text-2xl font-bold tracking-tight text-slate-900">{{ $inBookingCurrency($balance) }}</span>
                                    <span class="mt-1 block text-xs text-slate-500">Nothing more to pay before you travel.</span>
                                </label>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="mb-3 font-semibold text-slate-900">2. Choose a payment method</legend>
                            <div class="space-y-2">
                                @foreach ($gateways as $gateway)
                                    @php $charge = $gateway->chargeCurrency($booking); @endphp
                                    <label class="flex cursor-pointer items-center gap-4 rounded-2xl p-4 ring-1 transition" :class="gateway === '{{ $gateway->key() }}' ? 'bg-brand-50 ring-2 ring-brand-700' : 'bg-white ring-slate-200 hover:ring-slate-300'">
                                        <input type="radio" name="gateway" value="{{ $gateway->key() }}" x-model="gateway" class="size-4 accent-brand-700">
                                        <span class="min-w-0 flex-1">
                                            <span class="block font-semibold text-slate-900">{{ $gateway->name() }}</span>
                                            <span class="block text-sm text-slate-500">{{ $gateway->description() }}</span>
                                        </span>
                                        @if ($charge !== ($booking->currency ?? currencies()->base()->code))
                                            <span class="shrink-0 rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-600">Charged in {{ $charge }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <div>
                            <button class="btn btn-primary w-full py-4 text-base"><x-icon name="shield" class="size-5"/>Continue to secure payment</button>
                            <p class="mt-3 text-center text-xs text-slate-500">You’ll complete payment on the provider’s secure page. We never see or store your card details.</p>
                        </div>
                    </form>
                @endif
            </div>

            <aside class="lg:col-span-2">
                <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-200 lg:sticky lg:top-24">
                    <x-media :src="$booking->package?->hero_image_url" class="aspect-[16/9] rounded-xl"/>
                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Start date</dt><dd class="font-medium text-slate-900">{{ $booking->start_date?->format('j M Y') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Travellers</dt><dd class="font-medium text-slate-900">{{ $booking->travelers }}</dd></div>
                        <div class="flex justify-between gap-4 border-t border-slate-100 pt-3"><dt class="text-slate-500">Trip total</dt><dd class="font-semibold text-slate-900">{{ $inBookingCurrency($booking->total_estimate) }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Paid so far</dt><dd class="font-medium text-brand-700">{{ $inBookingCurrency($booking->amount_paid) }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Balance</dt><dd class="font-semibold text-slate-900">{{ $inBookingCurrency($balance) }}</dd></div>
                    </dl>
                    @if ($booking->payments->isNotEmpty())
                        <ul class="mt-5 space-y-2 border-t border-slate-100 pt-4 text-xs">
                            @foreach ($booking->payments as $p)
                                <li class="flex justify-between gap-3"><span class="text-slate-500">{{ $p->created_at->format('j M') }} · {{ $p->gateway === '2checkout' ? '2Checkout' : ucfirst($p->gateway) }}</span><span @class(['font-medium', 'text-brand-700' => $p->status === 'paid', 'text-slate-500' => $p->status !== 'paid'])>{{ $p->formattedAmount() }} · {{ $p->status }}</span></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </aside>
        </div>
    </section>
</x-site-layout>
