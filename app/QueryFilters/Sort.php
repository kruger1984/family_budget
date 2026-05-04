<?php

declare(strict_types=1);

namespace App\QueryFilters;

use Illuminate\Database\Eloquent\Builder;

class Sort extends Filter
{
    protected function applyFilter(Builder $builder): Builder
    {
        $field = request('sort.field', 'created_at');
        $order = request('sort.order', 'desc');

        return $builder->orderBy($field, $order);
    }

    protected function filterName(): string
    {
        return 'sort';
    }
}
