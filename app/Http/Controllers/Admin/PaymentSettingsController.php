<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Payments\PaymentGateways;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSettingsController extends Controller
{
    public function edit(PaymentGateways $gateways): View
    {
        return view('admin.payments', ['gateways' => $gateways->all()]);
    }

    public function update(Request $request, PaymentGateways $gateways): RedirectResponse
    {
        $rules = ['deposit_percent' => 'required|integer|min:1|max:100'];
        foreach ($gateways->all() as $gateway) {
            foreach ($gateway->fields() as $field => $definition) {
                $rules["{$gateway->key()}.{$field}"] = $definition['type'] === 'select'
                    ? 'nullable|in:'.implode(',', array_keys($definition['options']))
                    : 'nullable|string|max:500';
            }
        }
        $request->validate($rules);

        Setting::put(['deposit_percent' => (string) $request->integer('deposit_percent')]);

        foreach ($gateways->all() as $gateway) {
            $key = $gateway->key();
            Setting::put(["payment_{$key}_enabled" => $request->boolean("{$key}.enabled") ? '1' : '0']);

            foreach ($gateway->fields() as $field => $definition) {
                $value = $request->input("{$key}.{$field}");
                if ($definition['type'] === 'secret') {
                    // Blank means "keep the saved secret"; secrets are never sent back to the browser.
                    if (filled($value)) {
                        Setting::putSecret("payment_{$key}_{$field}", trim($value));
                    } elseif ($request->boolean("{$key}.clear_{$field}")) {
                        Setting::putSecret("payment_{$key}_{$field}", null);
                    }
                } else {
                    Setting::put(["payment_{$key}_{$field}" => filled($value) ? trim($value) : null]);
                }
            }
        }

        return back()->with('status', 'Payment settings saved.');
    }
}
