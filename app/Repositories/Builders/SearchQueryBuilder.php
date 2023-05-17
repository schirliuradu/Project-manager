<?php

namespace App\Repositories\Builders;

use App\Models\Enums\SortingValues;
use App\Models\Enums\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SearchQueryBuilder
{
    /**
     * @param Builder $builder
     */
    public function __construct(protected Builder $builder)
    {
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function withProject(string $id): self
    {
        $this->builder->where('project_id', '=', $id);

        return $this;
    }

    /**
     * @param Request $request
     *
     * @return SearchQueryBuilder
     */
    public function withStatus(Request $request): self
    {
        // handle STATUS filters
        if ($request->input('withClosed')) {
            $this->builder->whereIn('status', Status::basicValues());
        } else {
            $this->builder->where('status', '=', $request->input('onlyClosed')
                ? Status::CLOSED->value
                : Status::OPEN->value);
        }

        return $this;
    }

    /**
     * @param Request $request
     *
     * @return SearchQueryBuilder
     */
    public function withSorting(Request $request): self
    {
        if ($sortBy = $request->input('sortBy')) {
            switch ($sortBy) {
                case SortingValues::ALPHADESC->value:
                    $this->builder->orderBy('title', 'desc');
                    break;

                case SortingValues::ALPHAASC->value:
                    $this->builder->orderBy('title');
                    break;

                case SortingValues::UPDATE->value:
                    $this->builder->orderBy('updated_at');
                    break;

                case SortingValues::CREATE->value:
                default:
                    $this->builder->orderBy('created_at');
                    break;
            }
        }

        return $this;
    }
}