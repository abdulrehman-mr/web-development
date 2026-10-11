<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Setting;
use App\Support\CurrencyManager;
use App\Support\ExchangeRates;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class CurrencyController extends Controller
{
    public function index(CurrencyManager $currencies): View
    {
        return view('admin.currencies', [
            'all' => Currency::orderByDesc('is_enabled')->orderBy('code')->get(),
            'base' => $currencies->base(),
            'lastUpdate' => Currency::max('rate_updated_at'),
        ]);
    }

    public function update(Request $request, CurrencyManager $currencies): RedirectResponse
    {
        $codes = Currency::pluck('code')->all();
        // Changed rows arrive as one JSON field: posting ~800 separate inputs trips
        // web-application firewalls (too many arguments) and PHP's max_input_vars.
        $request->validate(['currencies_json' => 'nullable|json']);
        if ($request->filled('currencies_json')) {
            $request->merge(['currencies' => (array) json_decode($request->input('currencies_json'), true)]);
        }

        $data = $request->validate([
            'base_currency' => ['required', Rule::in($codes)],
            'default_currency' => ['required', Rule::in($codes)],
            'secondary_currency' => ['nullable', Rule::in($codes)],
            'currency_switcher' => 'nullable|boolean',
            'currencies' => 'nullable|array',
            'currencies.*.symbol' => 'required|string|max:12',
            'currencies.*.symbol_position' => 'required|in:before,after',
            'currencies.*.rate' => 'nullable|numeric|gt:0',
            'currencies.*.is_enabled' => 'nullable|boolean',
        ]);

        $oldBase = $currencies->base()->code;
        foreach ($data['currencies'] ?? [] as $code => $row) {
            if (! in_array($code, $codes, true)) {
                continue;
            }
            $rate = $code === $oldBase ? 1 : ($row['rate'] ?? null);
            Currency::where('code', $code)->first()?->update([
                'symbol' => $row['symbol'],
                'symbol_position' => $row['symbol_position'],
                'rate' => $rate,
                'is_enabled' => ! empty($row['is_enabled']) && $rate > 0,
            ]);
        }

        if ($data['base_currency'] !== $oldBase) {
            $currencies->rebase($data['base_currency']);
        }

        Currency::whereIn('code', array_filter([$data['base_currency'], $data['default_currency']]))->update(['is_enabled' => true]);
        Setting::put([
            'base_currency' => $data['base_currency'],
            'default_currency' => $data['default_currency'],
            'secondary_currency' => $data['secondary_currency'] ?? null,
            'currency_switcher' => $request->boolean('currency_switcher') ? '1' : '0',
        ]);
        Currency::forgetCache();

        return back()->with('status', 'Currency settings saved.');
    }

    public function refreshRates(ExchangeRates $rates): RedirectResponse
    {
        try {
            $count = $rates->update();
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['rates' => 'Could not fetch live rates: '.$e->getMessage()]);
        }

        return back()->with('status', "Updated {$count} exchange rates.");
    }
}
