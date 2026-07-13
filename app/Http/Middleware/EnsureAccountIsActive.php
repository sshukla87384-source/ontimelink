<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Frozen accounts may look but not act; banned accounts are logged out.
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($user->status === \App\Models\User::STATUS_BANNED) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'This account has been suspended.']);
        }

        if ($user->status === \App\Models\User::STATUS_FROZEN && ! $request->isMethodSafe()) {
            abort(403, 'Your account is frozen. Contact support.');
        }

        return $next($request);
    }
}
