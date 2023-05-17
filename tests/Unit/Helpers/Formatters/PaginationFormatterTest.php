<?php

namespace Tests\Unit\Helpers\Formatters;

use App\Helpers\Formatters\PaginationFormatter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Helpers\Formatters\PaginationFormatter
 */
class PaginationFormatterTest extends TestCase
{
    /**
     * @test
     * @covers ::format
     */
    public function should_format_pagination_object_into_a_custom_array(): void
    {
        $paginatorMock = \Mockery::mock(LengthAwarePaginator::class);
        $paginatorMock->shouldReceive('currentPage')->andReturn(1);
        $paginatorMock->shouldReceive('firstItem')->andReturn(1);
        $paginatorMock->shouldReceive('lastPage')->andReturn(2);
        $paginatorMock->shouldReceive('perPage')->andReturn(10);
        $paginatorMock->shouldReceive('lastItem')->andReturn(20);
        $paginatorMock->shouldReceive('total')->andReturn(20);

        $this->assertEquals([
            'current_page' => 1,
            'from' => 1,
            'last_page' => 2,
            'per_page' => 10,
            'to' => 20,
            'total' => 20,
        ], (new PaginationFormatter())->format($paginatorMock));
    }
}
