<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\Currency;
use App\Enums\TransactionType;
use App\Observers\TransactionObserver;
use App\Support\ValueObjects\Money;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property TransactionType $type
 * @property Currency $currency
 * @property Currency $target_currency
 * @property Money $amount
 * @property Money|null $target_amount
 * @property-read Account|null $account
 * @property-read Category|null $category
 * @property-read int|float $exchange_rate
 * @property-read Account|null $targetAccount
 * @property-read User|null $user
 *
 * @method static TransactionFactory factory($count = null, $state = [])
 * @method static Builder<static>|Transaction newModelQuery()
 * @method static Builder<static>|Transaction newQuery()
 * @method static Builder<static>|Transaction query()
 *
 * @mixin Eloquent
 *
 * @property int $id
 * @property int $user_id
 * @property int $account_id
 * @property int|null $target_account_id
 * @property int|null $category_id
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder<static>|Transaction whereAccountId($value)
 * @method static Builder<static>|Transaction whereAmount($value)
 * @method static Builder<static>|Transaction whereCategoryId($value)
 * @method static Builder<static>|Transaction whereCreatedAt($value)
 * @method static Builder<static>|Transaction whereCurrency($value)
 * @method static Builder<static>|Transaction whereDescription($value)
 * @method static Builder<static>|Transaction whereId($value)
 * @method static Builder<static>|Transaction whereTargetAccountId($value)
 * @method static Builder<static>|Transaction whereTargetAmount($value)
 * @method static Builder<static>|Transaction whereTargetCurrency($value)
 * @method static Builder<static>|Transaction whereType($value)
 * @method static Builder<static>|Transaction whereUpdatedAt($value)
 * @method static Builder<static>|Transaction whereUserId($value)
 *
 * @mixin Model
 * @mixin Model
 * @mixin Model
 * @mixin Model
 */
#[ObservedBy(TransactionObserver::class)]
#[Fillable([
    'id',
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
])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'id',
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

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'currency' => Currency::class,
            'target_currency' => Currency::class,
            'amount' => MoneyCast::class,
            'target_amount' => MoneyCast::class.':target_currency',
        ];
    }

    protected function getExchangeRateAttribute(): float|int
    {
        if ($this->type !== TransactionType::Transfer || ! $this->target_amount) {
            return 0;
        }

        return $this->amount->raw() / $this->target_amount->raw();
    }
}
