<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Database\Factories\FamilyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $owner_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Account> $accounts
 * @property-read int|null $accounts_count
 * @property-read Collection<int, User> $members
 * @property-read int|null $members_count
 *
 * @method static FamilyFactory factory($count = null, $state = [])
 * @method static Builder<static>|Family newModelQuery()
 * @method static Builder<static>|Family newQuery()
 * @method static Builder<static>|Family query()
 * @method static Builder<static>|Family whereCreatedAt($value)
 * @method static Builder<static>|Family whereId($value)
 * @method static Builder<static>|Family whereName($value)
 * @method static Builder<static>|Family whereOwnerId($value)
 * @method static Builder<static>|Family whereUpdatedAt($value)
 *
 * @mixin Eloquent
 * @mixin Model
 * @mixin Model
 * @mixin Model
 *
 * @property-read User $owner
 *
 * @mixin Model
 */
#[Fillable([
    'name',
    'owner_id',
])]
class Family extends Model
{
    /** @use HasFactory<FamilyFactory> */
    use HasFactory;

    protected $fillable = ['id', 'name', 'owner_id'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps()->withCasts([
            'role' => Role::class,
        ]);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }
}
