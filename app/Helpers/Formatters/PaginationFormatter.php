<?php

namespace App\Helpers\Formatters;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaginationFormatter
{
    /**
     * Format pagination results to array.
     *
     * @param LengthAwarePaginator $paginator
     *
     * @return array
     */
    public function format(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'from' => $paginator->firstItem(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
        ];
    }
}