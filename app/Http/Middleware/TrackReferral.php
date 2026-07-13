<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Remembers ?ref=CODE in the session for 30 minutes so the code survives
 * navigation between landing page and the registration form.
 */
class TrackReferral
{
    public function handle(Request $request, Closure $next): Response
    {
        $ref = $request->query('ref');

        if (is_string($ref) && preg_match('/^[A-Z0-9]{4,20}$/i', $ref)) {
            $request->session()->put('referral_code', strtoupper($ref));
        }

        return $next($request);
    }
}
