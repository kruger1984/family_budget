<?php

namespace App\Models;

use App\Enums\CategoryType;
use App\Exceptions\CategoryChildTypeException;
use App\Exceptions\CategoryNestingException;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

/**
 * @property CategoryType $type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read \App\Models\Family|null $family
 * @property-read Category|null $parent
 * @method static \Database\Factories\CategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @mixin \Eloquent
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'icon',
        'color',
        'parent_id',
        'family_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => CategoryType::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if ($category->parent_id) {
                if ($category->id && $category->parent_id === $category->id) {
                    throw new InvalidArgumentException('A category cannot be its own parent.');
                }

                $parent = $category->parent;

                if ($parent) {
                    if ($parent->type !== $category->type) {
                        throw new CategoryChildTypeException('Child category must have the same type as parent.');
                    }

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
