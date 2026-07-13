<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Services\LinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RedeemController extends Controller
{
    public function __construct(private readonly LinkService $links)
    {
    }

    /**
     * Public redemption endpoint. Exactly one visitor ever gets redirected;
     * everyone after (and every retry) lands on "Already Redeemed".
     */
    public function show(Request $request, string $token): RedirectResponse|View|Response
    {
        // Reject malformed tokens before touching the database.
        if (! preg_match('/^[a-f0-9]{64}$/', $token)) {
            return response()->view('errors.invalid-link', [], 404);
        }

        $destination = $this->links->redeem($token, $request->ip());

        if ($destination !== null) {
            return redirect()->away($destination, 302, ['Referrer-Policy' => 'no-referrer']);
        }

        $link = $this->links->findByToken($token);

        if ($link === null) {
            return response()->view('errors.invalid-link', [], 404);
        }

        if ($link->isExpired()) {
            return response()->view('errors.expired-link', [], 410);
        }

        return response()->view('redeem.already-redeemed', ['redeemedAt' => $link->redeemed_at], 410);
    }
}
