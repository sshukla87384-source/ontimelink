<?php

namespace App\Services;

use App\Models\PointTransaction;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    public function __construct(
        private readonly PointService $points,
        private readonly AuditService $audit,
    ) {
    }

    /**
     * Attach a pending referral at registration time.
     * Rewards are only paid after the new user verifies their email,
     * which blocks throwaway fake-account farming.
     */
    public function attach(User $newUser, string $referralCode, ?string $registrationIp = null): ?Referral
    {
        $referrer = User::where('referral_code', strtoupper(trim($referralCode)))->first();

        if ($referrer === null || $referrer->id === $newUser->id) {
            return null; // unknown code or self-referral
        }

        // Same-IP registrations are a strong multi-account signal: record
        // the referral for audit but mark it rejected (no rewards).
        $sameIp = $registrationIp !== null
            && $referrer->registration_ip_hash === AuditService::hashIp($registrationIp);

        return Referral::firstOrCreate(
            ['referred_user_id' => $newUser->id], // unique => one referral per user, ever
            [
                'referrer_id' => $referrer->id,
                'status' => $sameIp ? Referral::STATUS_REJECTED : Referral::STATUS_PENDING,
            ],
        );
    }

    /**
     * Pay out once the referred user verifies email. Idempotent: the status
     * transition happens under a row lock, so double verification events
     * cannot double-pay.
     */
    public function reward(User $referredUser): void
    {
        DB::transaction(function () use ($referredUser) {
            $referral = Referral::where('referred_user_id', $referredUser->id)
                ->lockForUpdate()
                ->first();

            if ($referral === null || $referral->status !== Referral::STATUS_PENDING) {
                return;
            }

            $referrerPoints = config('onetimelink.points.referrer_reward');
            $referredPoints = config('onetimelink.points.referred_bonus');

            $this->points->credit(
                $referral->referrer,
                $referrerPoints,
                PointTransaction::TYPE_REFERRAL,
                "Referral reward: {$referredUser->email} joined",
                $referral,
            );

            $this->points->credit(
                $referredUser,
                $referredPoints,
                PointTransaction::TYPE_REFERRAL,
                'Welcome bonus for joining via referral',
                $referral,
            );

            $referral->update([
                'status' => Referral::STATUS_REWARDED,
                'referrer_points' => $referrerPoints,
                'referred_points' => $referredPoints,
                'rewarded_at' => now(),
            ]);

            $this->audit->log('referral.rewarded', 'activity', $referral, userId: $referral->referrer_id);
        });
    }
}
