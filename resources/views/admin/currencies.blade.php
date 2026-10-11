<x-admin-layout title="Currencies">
    <x-slot:actions><button form="currency-form" class="btn btn-primary py-2.5">Save changes</button></x-slot:actions>

    <form id="currency-form" method="post" action="{{ route('admin.currencies.update') }}" class="space-y-6" x-data="{ q: '', onlyEnabled: false, changed: {} }">
        @csrf @method('PUT')
        <input type="hidden" name="currencies_json" :value="JSON.stringify(changed)">

        <div class="grid gap-6 xl:grid-cols-3">
            <x-admin.card class="xl:col-span-2" title="Site currency" description="Journey prices are entered in the base currency. Visitors can switch to any enabled currency and see converted prices.">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="f_base" class="label">Base currency (prices are entered in)</label>
                        <select id="f_base" name="base_currency" class="field">
                            @foreach ($all->sortBy('code') as $c)
                                <option value="{{ $c->code }}" @selected($base->code === $c->code)>{{ $c->label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Changing this re-calculates exchange rates but does not convert existing journey prices.</p>
                    </div>
                    <div>
                        <label for="f_default" class="label">Default currency for visitors</label>
                        <select id="f_default" name="default_currency" class="field">
                            @foreach ($all->sortBy('code') as $c)
                                <option value="{{ $c->code }}" @selected(setting('default_currency', $base->code) === $c->code)>{{ $c->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="f_secondary" class="label">Also show an approximate price in</label>
                        <select id="f_secondary" name="secondary_currency" class="field">
                            <option value="">— None —</option>
                            @foreach ($all->sortBy('code') as $c)
                                <option value="{{ $c->code }}" @selected(setting('secondary_currency') === $c->code)>{{ $c->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="self-end pb-2">
                        <x-admin.toggle name="currency_switcher" label="Show currency switcher in the header" :checked="setting('currency_switcher', '1') === '1'"/>
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card title="Exchange rates" description="Rates are units of each currency per 1 {{ $base->code }}. They update automatically every day when the scheduler runs.">
                <p class="text-sm text-slate-600">Last updated: <span class="font-semibold text-slate-900">{{ $lastUpdate ? \Illuminate\Support\Carbon::parse($lastUpdate)->diffForHumans() : 'never (starter rates)' }}</span></p>
                <button form="rates-form" class="btn btn-ghost mt-4 w-full"><x-icon name="globe" class="size-4"/>Update rates now</button>
                <p class="mt-2 text-xs text-slate-500">Source: <a href="https://www.exchangerate-api.com" class="underline" target="_blank" rel="noopener">ExchangeRate-API</a>. You can also type rates by hand below.</p>
            </x-admin.card>
        </div>

        <x-admin.card>
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-semibold text-slate-900">All currencies ({{ $all->count() }})</h2>
                    <p class="text-sm text-slate-500">Enable the ones visitors can choose, and set the symbol shown with prices.</p>
                </div>
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" x-model="onlyEnabled" class="size-4 accent-brand-700"> Enabled only</label>
                    <input type="search" x-model="q" placeholder="Search code or name…" class="field w-56 py-2">
                </div>
            </div>
            <div class="-mx-5 overflow-x-auto sm:-mx-6">
                <table class="w-full min-w-[46rem] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs text-slate-500">
                        <tr><th class="px-5 py-2 font-medium sm:px-6">On</th><th class="py-2 font-medium">Currency</th><th class="py-2 font-medium">Symbol</th><th class="py-2 font-medium">Position</th><th class="py-2 font-medium">Rate per 1 {{ $base->code }}</th><th class="py-2 pr-5 font-medium">Preview</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($all as $c)
                            @php $isBase = $c->code === $base->code; @endphp
                            <tr x-data="{ code: @js($c->code), symbol: @js($c->symbol), position: @js($c->symbol_position === 'after' ? 'after' : 'before'), enabled: @js($c->is_enabled), rate: @js($isBase ? 1 : ($c->rate ? rtrim(rtrim(number_format($c->rate, 6, '.', ''), '0'), '.') : '')),
                                        record() { changed[this.code] = { symbol: this.symbol, symbol_position: this.position, rate: this.rate === '' ? null : this.rate, is_enabled: this.enabled ? 1 : 0 } } }"
                                @input="record()" @change="record()"
                                x-show="(!onlyEnabled || enabled) && (q === '' || @js(strtolower($c->code.' '.$c->name)).includes(q.toLowerCase()))">
                                <td class="px-5 py-2 sm:px-6"><input type="checkbox" x-model="enabled" class="size-4 accent-brand-700" aria-label="Enable {{ $c->code }}"></td>
                                <td class="py-2"><span class="font-mono font-semibold text-slate-900">{{ $c->code }}</span> <span class="text-slate-500">{{ $c->name }}</span> @if ($isBase)<span class="ml-1 rounded bg-brand-100 px-1.5 py-0.5 text-xs font-semibold text-brand-800">base</span>@endif</td>
                                <td class="py-2"><input x-model="symbol" maxlength="12" class="field w-20 px-2 py-1.5" aria-label="{{ $c->code }} symbol"></td>
                                <td class="py-2">
                                    <select class="field w-28 px-2 py-1.5" x-model="position" aria-label="{{ $c->code }} symbol position">
                                        <option value="before">Before</option>
                                        <option value="after">After</option>
                                    </select>
                                </td>
                                <td class="py-2">
                                    @if ($isBase)
                                        <span class="px-2 text-slate-500">1</span>
                                    @else
                                        <input type="number" step="any" min="0" x-model="rate" class="field w-32 px-2 py-1.5" placeholder="—" aria-label="{{ $c->code }} rate">
                                    @endif
                                </td>
                                <td class="py-2 pr-5 font-medium text-slate-900" x-text="position === 'after' ? '1,250 ' + symbol : symbol + (/\p{L}$/u.test(symbol) ? ' ' : '') + '1,250'"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </form>
    <form id="rates-form" method="post" action="{{ route('admin.currencies.rates') }}">@csrf</form>
</x-admin-layout>
