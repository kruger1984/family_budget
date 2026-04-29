<?php

declare(strict_types=1);

namespace App\Models;

use App\Exceptions\CategoryChildFamilyException;
use App\Exceptions\CategoryNestingException;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

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

                /** @var Category|null $parent */
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
