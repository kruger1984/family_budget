<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\AccountType;
use App\Enums\Currency;
use App\Support\ValueObjects\Money;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property AccountType $type
 * @property Currency $currency
 * @property Money $balance
 * @property-read Family|null $family
 * @property-read User|null $user
 * @method static AccountFactory factory($count = null, $state = [])
 * @method static Builder<static>|Account newModelQuery()
 * @method static Builder<static>|Account newQuery()
 * @method static Builder<static>|Account query()
 * @mixin Eloquent
 * @property int $id
 * @property string $name
 * @property int|null $family_id
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|Account whereBalance($value)
 * @method static Builder<static>|Account whereCreatedAt($value)
 * @method static Builder<static>|Account whereCurrency($value)
 * @method static Builder<static>|Account whereFamilyId($value)
 * @method static Builder<static>|Account whereId($value)
 * @method static Builder<static>|Account whereName($value)
 * @method static Builder<static>|Account whereType($value)
 * @method static Builder<static>|Account whereUpdatedAt($value)
 * @method static Builder<static>|Account whereUserId($value)
 * @mixin Model
 * @mixin Model
 * @mixin \Eloquent
 */
#[Fillable([
    'id',
    'name',
    'family_id',
    'user_id',
    'type',
    'currency',
    'balance',
])]
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'family_id',
        'user_id',
        'type',
        'currency',
        'balance',
    ];

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

    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'currency' => Currency::class,
            'balance' => MoneyCast::class,
        ];
    }
}
