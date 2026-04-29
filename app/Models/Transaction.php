<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\Currency;
use App\Enums\TransactionType;
use App\Observers\TransactionObserver;
use App\Support\ValueObjects\Money;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property TransactionType $type
 * @property Currency $currency
 * @property Currency|null $target_currency
 * @property Money $amount
 * @property Money|null $target_amount
 */
#[ObservedBy(TransactionObserver::class)]

class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'target_account_id',
        'category_id',
        'type',
        'amount',
        'currency',
        'target_amount',
        'target_currency',
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
