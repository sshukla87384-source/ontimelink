<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('referrals.index', [
            'referralUrl' => $user->referralUrl(),
            'referrals' => $user->referralsMade()->with('referredUser:id,name,created_at')->latest()->paginate(15),
            'totalEarned' => (int) $user->referralsMade()->where('status', 'rewarded')->sum('referrer_points'),
        ]);
    }
}
