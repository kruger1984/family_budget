<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionType;
use App\Exceptions\CategoryChildFamilyException;
use App\Exceptions\CategoryNestingException;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * @property TransactionType $type
 * @property-read Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read Family|null $family
 * @property-read Category|null $parent
 *
 * @method static CategoryFactory factory($count = null, $state = [])
 * @method static Builder<static>|Category newModelQuery()
 * @method static Builder<static>|Category newQuery()
 * @method static Builder<static>|Category query()
 *
 * @mixin Eloquent
 *
 * @property int $id
 * @property string $name
 * @property string|null $icon
 * @property string|null $color
 * @property int|null $parent_id
 * @property int|null $family_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder<static>|Category whereColor($value)
 * @method static Builder<static>|Category whereCreatedAt($value)
 * @method static Builder<static>|Category whereFamilyId($value)
 * @method static Builder<static>|Category whereIcon($value)
 * @method static Builder<static>|Category whereId($value)
 * @method static Builder<static>|Category whereName($value)
 * @method static Builder<static>|Category whereParentId($value)
 * @method static Builder<static>|Category whereUpdatedAt($value)
 *
 * @mixin Model
 * @mixin Model
 * @mixin Model
 * @mixin Model
 */
#[Fillable([
    'id',
    'name',
    'icon',
    'color',
    'parent_id',
    'family_id',
])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'icon',
        'color',
        'parent_id',
        'family_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Category $category): void {
            if ($category->parent_id) {
                throw_if(
                    $category->id && $category->parent_id === $category->id,
                    InvalidArgumentException::class,
                    'A category cannot be its own parent.'
                );
                $parent = $category->parent;
                throw_if(
                    $parent && $parent->parent_id !== null,
                    CategoryNestingException::class,
                    'The selected parent is already a subcategory.'
                );
                throw_if(
                    $category->exists && $category->children()->exists(),
                    CategoryNestingException::class,
                    'This category has subcategories and cannot become a subcategory itself.'
                );
                throw_if(
                    $parent->family_id !== $category->family_id,
                    CategoryChildFamilyException::class
                );
            }
        });
    }
}
