<?php

namespace App\Repositories;

use App\Models\Enums\SortingValues;
use App\Models\Enums\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait SearchTrait
{
    /**
     * @param Request $request
     * @param Builder $builder
     *
     * @return void
     */
    private function bindStatusFilterLogic(Request $request, Builder $builder): void
    {
        // handle STATUS filters
        $builder->when($request->input('withClosed'), function ($builder) {
            $builder->whereIn('status', Status::basicValues());
        }, function ($builder) use ($request) {
            $builder->where('status', '=', $request->input('onlyClosed')
                ? Status::CLOSED->value
                : Status::OPEN->value
            );
        });
    }

    /**
     * @param Request $request
     * @param Builder $builder
     *
     * @return void
     */
    private function bindSortingFilterLogic(Request $request, Builder $builder): void
    {
        if ($sortBy = $request->input('sortBy')) {
            switch ($sortBy) {
                case SortingValues::ALPHADESC->value:
                    $builder->orderBy('title', 'desc');
                    break;

                case SortingValues::ALPHAASC->value:
                    $builder->orderBy('title');
                    break;

                case SortingValues::UPDATE->value:
                    $builder->orderBy('updated_at');
                    break;

                case SortingValues::CREATE->value:
                default:
                    $builder->orderBy('created_at');
                    break;
            }
        }
    }
}