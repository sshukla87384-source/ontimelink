<?php

namespace App\Http\Controllers;

use App\Services\Payments\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(Request $request, PaymentManager $payments): View
    {
        $wallet = $request->user()->wallet;

        return view('wallet.index', [
            'wallet' => $wallet,
            'transactions' => $wallet->transactions()->paginate(15),
            'payments' => $request->user()->payments()->limit(10)->get(),
            'gateways' => $payments->enabled(),
        ]);
    }

    public function purchase(Request $request, PaymentManager $payments): View
    {
        $validated = $request->validate([
            'gateway' => ['required', 'string', 'in:'.implode(',', array_keys($payments->enabled()))],
            'points' => ['required', 'integer', 'min:10', 'max:100000'],
        ]);

        [$payment, $instructions] = $payments->createPointsPurchase(
            $request->user(),
            $validated['gateway'],
            (int) $validated['points'],
        );

        return view('wallet.checkout', compact('payment', 'instructions'));
    }
}
