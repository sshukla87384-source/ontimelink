<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminLinkController extends Controller
{
    public function index(Request $request): View
    {
        $links = Link::query()
            ->with('user:id,uuid,name,email')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('user'), fn ($q, $u) => $q->whereRelation('user', 'email', 'like', "%{$u}%"))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.links.index', compact('links'));
    }

    public function disable(Request $request, Link $link, AuditService $audit): RedirectResponse
    {
        Link::whereKey($link->id)
            ->where('status', Link::STATUS_ACTIVE)
            ->update(['status' => Link::STATUS_DISABLED]);

        $audit->log('admin.link_disabled', 'admin', $link);

        return back()->with('status', 'Link disabled.');
    }

    public function export(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'wb');
            fputcsv($out, ['uuid', 'owner_email', 'status', 'created_at', 'redeemed_at', 'expires_at']);

            Link::with('user:id,email')->lazyById(500)->each(function (Link $l) use ($out) {
                fputcsv($out, [$l->uuid, $l->user?->email ?? 'guest', $l->status, $l->created_at, $l->redeemed_at, $l->expires_at]);
            });

            fclose($out);
        }, 'links-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }
}
