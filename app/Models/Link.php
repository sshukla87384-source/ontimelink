<?php

namespace App\Models;

use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Link extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_REDEEMED = 'redeemed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_DISABLED = 'disabled';

    protected $fillable = [
        'token_hash', 'destination_encrypted', 'user_id', 'guest_hash',
        'status', 'expires_at', 'label',
    ];

    // The encrypted destination must never leak through toArray()/toJson().
    protected $hidden = ['destination_encrypted', 'token_hash'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'redeemed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Decrypted destination. Available only in-process; never persisted.
     */
    protected function destination(): Attribute
    {
        return Attribute::make(
            get: fn () => Crypt::decryptString($this->destination_encrypted),
        )->shouldCache();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function scopeRedeemed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REDEEMED);
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || ($this->expires_at !== null && $this->expires_at->isPast() && $this->status === self::STATUS_ACTIVE);
    }
}
