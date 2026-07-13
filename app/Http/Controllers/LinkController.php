<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientPointsException;
use App\Http\Requests\StoreBulkLinksRequest;
use App\Http\Requests\StoreLinkRequest;
use App\Models\Link;
use App\Services\LinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function __construct(private readonly LinkService $links)
    {
    }

    public function index(Request $request): View
    {
        $links = $request->user()->links()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('links.index', compact('links'));
    }

    public function create(Request $request): View
    {
        $guestBlocked = $request->user() === null && $this->links->guestQuotaUsed($request->ip());

        return view('links.create', compact('guestBlocked'));
    }

    public function store(StoreLinkRequest $request): RedirectResponse|View
    {
        $user = $request->user();

        if ($user === null && $this->links->guestQuotaUsed($request->ip())) {
            return redirect()->route('register')
                ->with('status', 'Your free link is used up - create an account for 10 free points.');
        }

        if ($user !== null && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $expiresAt = $request->validated('expires_in_days')
            ? now()->addDays((int) $request->validated('expires_in_days'))
            : null;

        try {
            [$link, $token] = $this->links->create(
                $request->validated('destination'),
                $user,
                $request->ip(),
                $expiresAt,
                $request->validated('label'),
            );
        } catch (InsufficientPointsException) {
            return redirect()->route('wallet.index')
                ->withErrors(['points' => 'You are out of points. Top up to keep generating links.']);
        }

        // The one and only time the redeem URL can be shown.
        return view('links.created', [
            'link' => $link,
            'redeemUrl' => route('redeem.show', ['token' => $token]),
        ]);
    }

    public function bulkCreate(): View
    {
        return view('links.bulk');
    }

    public function bulkStore(StoreBulkLinksRequest $request): View
    {
        $expiresAt = $request->validated('expires_in_days')
            ? now()->addDays((int) $request->validated('expires_in_days'))
            : null;

        [$results, $skipped] = $this->links->createBulk($request->urlList(), $request->user(), $expiresAt);

        $rows = array_map(fn (array $r) => [
            'row' => $r['row'],
            'url' => $r['url'],
            'redeemUrl' => route('redeem.show', ['token' => $r['token']]),
        ], $results);

        return view('links.bulk-result', ['rows' => $rows, 'skipped' => $skipped]);
    }

    public function disable(Request $request, Link $link): RedirectResponse
    {
        Gate::authorize('disable', $link);

        // Guarded transition: only an active link can be disabled.
        Link::whereKey($link->id)
            ->where('status', Link::STATUS_ACTIVE)
            ->update(['status' => Link::STATUS_DISABLED]);

        return back()->with('status', 'Link disabled.');
    }
}
