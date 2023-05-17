<?php

namespace App\Factories;

use App\Repositories\Builders\SearchQueryBuilder;
use Illuminate\Database\Eloquent\Builder;

class SearchQueryBuilderFactory
{
    /**
     * @param Builder $builder
     *
     * @return SearchQueryBuilder
     */
    public function create(Builder $builder): SearchQueryBuilder
    {
       return new SearchQueryBuilder($builder);
    }
}