<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointTransaction;
use App\Models\User;
use App\Services\AuditService;
use App\Services\PointService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->query('q'), fn ($q, $term) => $q->where(
                fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")
            ))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->withCount('links')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load('wallet');

        return view('admin.users.show', [
            'user' => $user,
            'links' => $user->links()->latest()->limit(10)->get(),
            'pointTransactions' => $user->pointTransactions()->limit(10)->get(),
            'walletTransactions' => $user->wallet->transactions()->limit(10)->get(),
        ]);
    }

    public function updateStatus(Request $request, User $user, AuditService $audit): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,frozen,banned'],
        ]);

        abort_if($user->isAdmin() && $validated['status'] !== User::STATUS_ACTIVE, 403,
            'Admin accounts cannot be frozen or banned from the panel.');

        $user->update(['status' => $validated['status']]);
        $audit->log('admin.user_status', 'admin', $user, ['status' => $validated['status']]);

        return back()->with('status', "User marked {$validated['status']}.");
    }

    public function adjustPoints(Request $request, User $user, PointService $points, AuditService $audit): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'not_in:0', 'min:-100000', 'max:100000'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $amount = (int) $validated['amount'];
        $description = 'Admin adjustment: '.$validated['reason'];

        $amount > 0
            ? $points->credit($user, $amount, PointTransaction::TYPE_ADMIN, $description, performedBy: $request->user()->id)
            : $points->debit($user, abs($amount), PointTransaction::TYPE_ADMIN, $description, performedBy: $request->user()->id);

        $audit->log('admin.points_adjusted', 'admin', $user, ['amount' => $amount, 'reason' => $validated['reason']]);

        return back()->with('status', 'Points adjusted.');
    }

    public function adjustWallet(Request $request, User $user, WalletService $wallets, AuditService $audit): RedirectResponse
    {
        $validated = $request->validate([
            'amount_minor' => ['required', 'integer', 'not_in:0', 'min:-10000000', 'max:10000000'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $amount = (int) $validated['amount_minor'];
        $description = 'Admin adjustment: '.$validated['reason'];

        $amount > 0
            ? $wallets->credit($user->wallet, $amount, 'admin_adjustment', $description, performedBy: $request->user()->id, allowFrozen: true)
            : $wallets->debit($user->wallet, abs($amount), 'admin_adjustment', $description, performedBy: $request->user()->id);

        $audit->log('admin.wallet_adjusted', 'admin', $user, ['amount_minor' => $amount]);

        return back()->with('status', 'Wallet adjusted.');
    }

    public function toggleWalletFreeze(Request $request, User $user, WalletService $wallets): RedirectResponse
    {
        $wallets->setFrozen($user->wallet, ! $user->wallet->is_frozen, $request->user()->id);

        return back()->with('status', $user->wallet->fresh()->is_frozen ? 'Wallet frozen.' : 'Wallet unfrozen.');
    }

    public function restore(Request $request, string $uuid, AuditService $audit): RedirectResponse
    {
        $user = User::withTrashed()->where('uuid', $uuid)->firstOrFail();
        $user->restore();
        $audit->log('admin.user_restored', 'admin', $user);

        return back()->with('status', 'User restored.');
    }

    /**
     * Streamed CSV export - constant memory even with hundreds of
     * thousands of rows (lazy cursor, no collection buffering).
     */
    public function export(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'wb');
            fputcsv($out, ['uuid', 'name', 'email', 'status', 'points', 'registered_at']);

            User::query()->lazyById(500)->each(function (User $u) use ($out) {
                fputcsv($out, [$u->uuid, $u->name, $u->email, $u->status, $u->points_balance, $u->created_at]);
            });

            fclose($out);
        }, 'users-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }
}
