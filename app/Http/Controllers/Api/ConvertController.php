<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Utils\Currencies\Currencies;
use Illuminate\Http\Request;

class ConvertController extends Controller
{
    public function getRate(Request $request) {
        $validatedData = $request->validate([
            'symbol_from' => 'required|string',
            'symbol_to' => 'required|string'
        ]);

        $instance = new Currencies();
        $rate = $instance->getRate($validatedData['symbol_from'], $validatedData['symbol_to']);
        $rateReversed = $instance->getRate($validatedData['symbol_to'], $validatedData['symbol_from']);
        $history = $instance->getHistory($validatedData['symbol_from'], $validatedData['symbol_to'], '-1 week');

        return response()->json([
            'rate' => $rate,
            'rateReversed' => $rateReversed,
            'history' => $history,
        ]);
    }
}
