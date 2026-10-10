<?php

namespace App\Http\Controllers;

use App\Support\CurrencyManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /** Let the visitor browse prices in another enabled currency. */
    public function update(Request $request, CurrencyManager $currencies): RedirectResponse
    {
        $code = strtoupper((string) $request->input('currency'));

        if ($currencies->enabled()->has($code)) {
            session([CurrencyManager::SESSION_KEY => $code]);
        }

        return back();
    }
}
