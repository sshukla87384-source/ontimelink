<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceSecureHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $csp = "default-src 'self'; "
            ."script-src 'self' https://cdn.jsdelivr.net; "
            ."style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; "
            ."img-src 'self' data:; "
            ."font-src 'self' https://cdn.jsdelivr.net; "
            ."frame-ancestors 'none'; "
            ."base-uri 'self'; form-action 'self'; object-src 'none'";

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
