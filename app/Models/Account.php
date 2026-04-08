<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\AccountType;
use App\Enums\Currency;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property AccountType $type
 * @property Currency $currency
 * @property \App\Support\ValueObjects\Money $balance
 * @property-read \App\Models\Family|null $family
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\AccountFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account query()
 * @mixin \Eloquent
 */
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'family_id',
        'user_id',
        'type',
        'currency',
        'balance'
    ];

    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'currency' => Currency::class,
            'balance' => MoneyCast::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function isShared(): bool
    {
        return $this->family_id !== null;
    }
}

