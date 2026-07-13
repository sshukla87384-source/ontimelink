<?php

namespace App\Services;

use App\Exceptions\InsufficientPointsException;
use App\Models\Link;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class LinkService
{
    public function __construct(
        private readonly PointService $points,
        private readonly AuditService $audit,
    ) {
    }

    /**
     * Create a one-time link. Returns [Link, plainToken].
     *
     * The plain token exists only in this response; the database stores its
     * SHA-256 hash, so neither staff nor an attacker with DB access can
     * reconstruct a redeemable URL.
     */
    public function create(
        string $destinationUrl,
        ?User $user,
        ?string $guestIp = null,
        ?Carbon $expiresAt = null,
        ?string $label = null,
    ): array {
        $token = bin2hex(random_bytes(config('onetimelink.links.token_bytes')));

        $link = DB::transaction(function () use ($destinationUrl, $user, $guestIp, $expiresAt, $label, $token) {
            $link = Link::create([
                'token_hash' => hash('sha256', $token),
                'destination_encrypted' => Crypt::encryptString($destinationUrl),
                'user_id' => $user?->id,
                'guest_hash' => $user ? null : AuditService::hashIp($guestIp),
                'status' => Link::STATUS_ACTIVE,
                'expires_at' => $expiresAt,
                'label' => $label,
            ]);

            if ($user !== null) {
                $this->points->debit(
                    $user,
                    config('onetimelink.points.cost_per_link'),
                    PointTransaction::TYPE_SPEND,
                    'Generated one-time link',
                    $link,
                );
            }

            return $link;
        });

        $this->audit->log('link.created', 'activity', $link, ['label' => $label], $user?->id);

        return [$link, $token];
    }

    /**
     * Bulk generation. All-or-nothing is intentionally NOT used: each link is
     * its own transaction so a failure mid-batch (e.g. points run out) keeps
     * the links already paid for. Returns [results, errors].
     */
    public function createBulk(array $urls, User $user, ?Carbon $expiresAt = null): array
    {
        $results = [];
        $errors = [];
        $seen = [];

        foreach (array_values($urls) as $i => $url) {
            $url = trim($url);
            $row = $i + 1;

            if ($url === '') {
                continue;
            }
            if (isset($seen[$url])) {
                $errors[$row] = 'Duplicate of row '.$seen[$url].' - skipped.';
                continue;
            }
            if (! self::isAcceptableUrl($url)) {
                $errors[$row] = 'Not a valid http(s) URL - skipped.';
                continue;
            }

            try {
                [$link, $token] = $this->create($url, $user, expiresAt: $expiresAt);
                $results[] = ['row' => $row, 'url' => $url, 'link' => $link, 'token' => $token];
                $seen[$url] = $row;
            } catch (InsufficientPointsException) {
                $errors[$row] = 'Stopped: you ran out of points.';
                break;
            }
        }

        return [$results, $errors];
    }

    /**
     * Atomic redemption. The single UPDATE ... WHERE status='active' is the
     * race-condition guard: MySQL row locking guarantees exactly one request
     * can flip the status, no matter how many arrive simultaneously.
     *
     * Returns the destination URL on success, or null when the link was
     * already redeemed / expired / disabled.
     */
    public function redeem(string $token, ?string $visitorIp = null): ?string
    {
        $hash = hash('sha256', $token);

        $updated = Link::where('token_hash', $hash)
            ->where('status', Link::STATUS_ACTIVE)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->update([
                'status' => Link::STATUS_REDEEMED,
                'redeemed_at' => now(),
                'redeemed_ip_hash' => AuditService::hashIp($visitorIp),
            ]);

        if ($updated !== 1) {
            return null; // lost the race, already redeemed, expired, or unknown
        }

        $link = Link::where('token_hash', $hash)->first();
        $this->audit->log('link.redeemed', 'activity', $link, userId: $link->user_id);

        return $link->destination;
    }

    /**
     * Look up a link by token without mutating it (for status pages).
     */
    public function findByToken(string $token): ?Link
    {
        return Link::where('token_hash', hash('sha256', $token))->first();
    }

    public function guestQuotaUsed(?string $ip): bool
    {
        if ($ip === null) {
            return true;
        }

        return Link::where('guest_hash', AuditService::hashIp($ip))->count()
            >= config('onetimelink.guest.free_links');
    }

    /**
     * Nightly sweep: flag expired-but-active links so dashboards and admin
     * stats stay truthful. Redemption logic is already expiry-safe on its own.
     */
    public function markExpired(): int
    {
        return Link::where('status', Link::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => Link::STATUS_EXPIRED]);
    }

    public static function isAcceptableUrl(string $url): bool
    {
        if (strlen($url) > 2048 || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }
}
