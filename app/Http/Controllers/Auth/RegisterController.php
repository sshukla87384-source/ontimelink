<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\PointTransaction;
use App\Models\Setting;
use App\Models\User;
use App\Services\AuditService;
use App\Services\PointService;
use App\Services\ReferralService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        abort_unless(Setting::get('registration_open', '1') === '1', 403,
            'Registration is temporarily closed.');

        return view('auth.register');
    }

    public function store(
        RegisterRequest $request,
        PointService $points,
        ReferralService $referrals,
        AuditService $audit,
    ): RedirectResponse {
        abort_unless(Setting::get('registration_open', '1') === '1', 403,
            'Registration is temporarily closed.');

        $user = DB::transaction(function () use ($request, $points, $referrals) {
            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
                'registration_ip_hash' => AuditService::hashIp($request->ip()),
            ]);

            $points->credit(
                $user,
                config('onetimelink.points.signup_bonus'),
                PointTransaction::TYPE_BONUS,
                'Welcome bonus',
            );

            if ($code = $request->session()->pull('referral_code')) {
                $referrals->attach($user, $code, $request->ip());
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        $audit->log('auth.registered', 'security', $user, userId: $user->id);

        return redirect()->route('verification.notice');
    }
}
