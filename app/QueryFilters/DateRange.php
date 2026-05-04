<?php

declare(strict_types=1);

namespace App\QueryFilters;

use Illuminate\Database\Eloquent\Builder;

class DateRange extends Filter
{
    protected function applyFilter(Builder $builder): Builder
    {
        return $builder->whereBetween('created_at', [request('from'), request('to')]);
    }

    protected function filterName(): string
    {
        return 'from';
    }
}
