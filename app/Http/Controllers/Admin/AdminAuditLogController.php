<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->with('user:id,uuid,name,email')
            ->when($request->query('category'), fn ($q, $c) => $q->where('category', $c))
            ->when($request->query('event'), fn ($q, $e) => $q->where('event', 'like', "%{$e}%"))
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit.index', compact('logs'));
    }
}
