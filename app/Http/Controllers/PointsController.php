<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PointsController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard.points', [
            'balance' => $request->user()->points_balance,
            'transactions' => $request->user()->pointTransactions()->paginate(20),
        ]);
    }
}
