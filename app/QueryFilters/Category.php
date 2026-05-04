<?php

declare(strict_types=1);

namespace App\QueryFilters;

use Illuminate\Database\Eloquent\Builder;

class Category extends Filter
{
    protected function applyFilter(Builder $builder): Builder
    {
        return $builder->where('category_id', request('category'));
    }
}
