<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\Currency;
use App\Enums\TransactionType;
use App\Observers\TransactionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property TransactionType $type
 * @property Currency $currency
 * @property Currency $target_currency
 * @property \App\Support\ValueObjects\Money $amount
 * @property \App\Support\ValueObjects\Money $target_amount
 * @property-read \App\Models\Account|null $account
 * @property-read \App\Models\Category|null $category
 * @property-read int|float $exchange_rate
 * @property-read \App\Models\Account|null $targetAccount
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\TransactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction query()
 * @mixin \Eloquent
 */

#[ObservedBy(TransactionObserver::class)]
class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;


    protected $fillable = [
        'user_id',
        'type',

        'account_id',
        'currency',
        'amount',

        'target_account_id',
        'target_amount',
        'target_currency',

        'category_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type'            => TransactionType::class,
            'currency'        => Currency::class,
            'target_currency' => Currency::class,
            'amount'          => MoneyCast::class,
            'target_amount'   => MoneyCast::class . ':target_currency',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function targetAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'target_account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }


    public function getExchangeRateAttribute(): float|int
    {
        if ($this->type !== TransactionType::Transfer || ! $this->target_amount) {
            return 0;
        }

        return $this->amount->raw() / $this->target_amount->raw();
    }
}
