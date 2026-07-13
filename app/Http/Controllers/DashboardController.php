<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Link;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load('wallet');

        $linkStats = Link::where('user_id', $user->id)
            ->selectRaw("count(*) as total,
                sum(status = 'active') as active,
                sum(status = 'redeemed') as redeemed")
            ->first();

        $referralEarnings = (int) Referral::where('referrer_id', $user->id)
            ->where('status', Referral::STATUS_REWARDED)
            ->sum('referrer_points');

        return view('dashboard.index', [
            'user' => $user,
            'linkStats' => $linkStats,
            'referralEarnings' => $referralEarnings,
            'recentLinks' => $user->links()->latest()->limit(5)->get(),
            'recentPoints' => $user->pointTransactions()->limit(5)->get(),
            'recentActivity' => AuditLog::where('user_id', $user->id)->latest('created_at')->limit(8)->get(),
        ]);
    }
}
