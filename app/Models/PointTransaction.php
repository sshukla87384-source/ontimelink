<?php

namespace App\Models;

use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PointTransaction extends Model
{
    use HasFactory, HasUuid;

    public const TYPE_EARN = 'earn';
    public const TYPE_SPEND = 'spend';
    public const TYPE_REFUND = 'refund';
    public const TYPE_BONUS = 'bonus';
    public const TYPE_REFERRAL = 'referral';
    public const TYPE_ADMIN = 'admin_adjustment';

    protected $fillable = [
        'user_id', 'type', 'amount', 'balance_after', 'description',
        'reference_type', 'reference_id', 'performed_by',
    ];

    protected function casts(): array
    {
        return ['amount' => 'integer', 'balance_after' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
