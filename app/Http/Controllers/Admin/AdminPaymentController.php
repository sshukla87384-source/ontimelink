<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $payments = Payment::query()
            ->with('user:id,uuid,name,email')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('gateway'), fn ($q, $g) => $q->where('gateway', $g))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }
}
