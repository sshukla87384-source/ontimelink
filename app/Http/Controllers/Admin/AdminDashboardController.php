<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\Payment;
use App\Models\Referral;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'users_today' => User::whereDate('created_at', today())->count(),
                'links' => Link::count(),
                'links_redeemed' => Link::redeemed()->count(),
                'links_active' => Link::active()->count(),
                'referrals_rewarded' => Referral::where('status', 'rewarded')->count(),
                'revenue_minor' => (int) Payment::where('status', 'confirmed')->sum('amount'),
            ],
            'recentUsers' => User::latest()->limit(8)->get(),
            'recentPayments' => Payment::with('user:id,name,email')->latest()->limit(8)->get(),
        ]);
    }
}
