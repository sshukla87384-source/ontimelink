<?php

namespace App\Models;

use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['user_id', 'balance', 'currency', 'is_frozen'];

    protected function casts(): array
    {
        return ['balance' => 'integer', 'is_frozen' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    public function formattedBalance(): string
    {
        return number_format($this->balance / 100, 2).' '.$this->currency;
    }
}
