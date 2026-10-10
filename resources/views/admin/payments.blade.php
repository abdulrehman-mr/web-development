<x-admin-layout title="Payments">
    <x-slot:actions><button form="payments-form" class="btn btn-primary py-2.5">Save changes</button></x-slot:actions>

    <form id="payments-form" method="post" action="{{ route('admin.payments.update') }}" class="space-y-6">
        @csrf @method('PUT')

        <x-admin.card title="How guests pay" description="After requesting a journey, guests can pay a deposit or the full amount online. You can also send them a payment link from any booking.">
            <div class="max-w-xs">
                <x-admin.input name="deposit_percent" type="number" min="1" max="100" label="Deposit (% of the trip total)" :value="setting('deposit_percent', 25)"/>
            </div>
        </x-admin.card>

        @foreach ($gateways as $gateway)
            @php $key = $gateway->key(); @endphp
            <x-admin.card x-data="{ on: {{ setting('payment_'.$key.'_enabled') === '1' ? 'true' : 'false' }} }">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="flex items-center gap-2 font-semibold text-slate-900">
                            {{ $gateway->name() }}
                            @if ($gateway->isEnabled())
                                <span class="rounded-md bg-brand-100 px-2 py-0.5 text-xs font-semibold text-brand-800">Active{{ $gateway->sandbox() ? ' · test mode' : '' }}</span>
                            @elseif (setting('payment_'.$key.'_enabled') === '1')
                                <span class="rounded-md bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700">Missing credentials</span>
                            @endif
                        </h2>
                        <p class="mt-0.5 text-sm text-slate-500">{{ $gateway->description() }}</p>
                    </div>
                    <label class="flex cursor-pointer items-center gap-3">
                        <input type="checkbox" name="{{ $key }}[enabled]" value="1" x-model="on" class="peer sr-only">
                        <span class="relative h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-brand-700 after:absolute after:top-0.5 after:left-0.5 after:size-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
                        <span class="text-sm font-medium" x-text="on ? 'Enabled' : 'Disabled'"></span>
                    </label>
                </div>

                <div x-show="on" x-collapse class="mt-5 grid gap-4 border-t border-slate-100 pt-5 sm:grid-cols-2">
                    @foreach ($gateway->fields() as $field => $definition)
                        @php $id = "f_{$key}_{$field}"; @endphp
                        <div @class(['sm:col-span-2' => ! empty($definition['help']) && strlen($definition['help']) > 80])>
                            <label for="{{ $id }}" class="label">{{ $definition['label'] }}</label>
                            @if ($definition['type'] === 'select')
                                <select id="{{ $id }}" name="{{ $key }}[{{ $field }}]" class="field">
                                    @foreach ($definition['options'] as $value => $optionLabel)
                                        <option value="{{ $value }}" @selected(($gateway->config($field) ?? array_key_first($definition['options'])) === $value)>{{ $optionLabel }}</option>
                                    @endforeach
                                </select>
                            @elseif ($definition['type'] === 'secret')
                                @php $saved = filled($gateway->config($field)); @endphp
                                <input id="{{ $id }}" type="password" name="{{ $key }}[{{ $field }}]" autocomplete="new-password" class="field" placeholder="{{ $saved ? '•••••••• saved — leave blank to keep' : 'Not set' }}">
                                @if ($saved)
                                    <label class="mt-1.5 flex items-center gap-2 text-xs text-slate-500"><input type="checkbox" name="{{ $key }}[clear_{{ $field }}]" value="1" class="size-3.5 accent-rose-600"> Remove saved value</label>
                                @endif
                            @else
                                <input id="{{ $id }}" name="{{ $key }}[{{ $field }}]" value="{{ $gateway->config($field) }}" class="field">
                            @endif
                            @if (! empty($definition['help']))<p class="mt-1 text-xs text-slate-500">{{ $definition['help'] }}</p>@endif
                        </div>
                    @endforeach
                    <div class="sm:col-span-2" x-data="{ copied: false }">
                        <span class="label">Webhook / notification URL</span>
                        <div class="flex gap-2">
                            <input readonly value="{{ $gateway->webhookUrl() }}" class="field bg-slate-50 font-mono text-xs" aria-label="{{ $gateway->name() }} webhook URL">
                            <button type="button" class="btn btn-ghost shrink-0 py-2" @click="navigator.clipboard.writeText(@js($gateway->webhookUrl())); copied = true; setTimeout(() => copied = false, 1500)" x-text="copied ? 'Copied' : 'Copy'">Copy</button>
                        </div>
                    </div>
                </div>
            </x-admin.card>
        @endforeach

        <p class="text-xs text-slate-500">Secret keys are stored encrypted with your app key. Card details never touch this server: every provider uses its own secure checkout page.</p>
    </form>
</x-admin-layout>
