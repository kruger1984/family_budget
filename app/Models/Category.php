<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Exceptions\CategoryChildTypeException;
use App\Exceptions\CategoryNestingException;
use Database\Factories\CategoryFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

/**
 * @property TransactionType $type
 * @property-read Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read Family|null $family
 * @property-read Category|null $parent
 * @method static CategoryFactory factory($count = null, $state = [])
 * @method static Builder<static>|Category newModelQuery()
 * @method static Builder<static>|Category newQuery()
 * @method static Builder<static>|Category query()
 * @mixin Eloquent
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'color',
        'parent_id',
        'family_id',
    ];


    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if ($category->parent_id) {
                if ($category->id && $category->parent_id === $category->id) {
                    throw new InvalidArgumentException('A category cannot be its own parent.');
                }

                $parent = $category->parent;

                if ($parent) {

                    if ($parent->parent_id !== null) {
                        throw new CategoryNestingException('The selected parent is already a subcategory.');
                    }
                }

                if ($category->exists && $category->children()->exists()) {
                    throw new CategoryNestingException('This category has subcategories and cannot become a subcategory itself.');
                }
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}
