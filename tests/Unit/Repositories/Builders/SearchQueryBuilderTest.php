<?php

namespace Tests\Unit\Repositories\Builders;

use App\Models\Enums\SortingValues;
use App\Models\Enums\Status;
use App\Repositories\Builders\SearchQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Tests\Unit\UnitTestCase;

/**
 * @coversDefaultClass \App\Repositories\Builders\SearchQueryBuilder
 */
class SearchQueryBuilderTest extends UnitTestCase
{
    private Builder $queryMock;
    private SearchQueryBuilder $qb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queryMock = \Mockery::mock(Builder::class);
        $this->qb = new SearchQueryBuilder($this->queryMock);
    }

    /**
     * @test
     * @covers ::withProject
     */
    public function should_add_where_clause_with_project_id(): void
    {
        $fakeProjectId = 1111;

        $this->queryMock->shouldReceive('where')
            ->once()
            ->with('project_id', '=', $fakeProjectId)
            ->andReturnSelf();

        $this->qb->withProject($fakeProjectId);
    }

    /**
     * @test
     * @covers ::withStatus
     */
    public function should_add_where_in_statuses_array_if_with_closed_filter_is_set(): void
    {
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('input')
            ->once()
            ->with('withClosed')
            ->andReturn(1);

        $this->queryMock->shouldReceive('whereIn')
            ->once()
            ->with('status', Status::basicValues())
            ->andReturnSelf();

        $this->queryMock->shouldReceive('where')->never();

        $this->qb->withStatus($request);
    }

    /**
     * @test
     * @covers ::withStatus
     */
    public function should_add_where_clause_with_status_closed(): void
    {
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('input')
            ->once()
            ->with('withClosed')
            ->andReturnNull();

        $request->shouldReceive('input')
            ->once()
            ->with('onlyClosed')
            ->andReturn(1);

        $this->queryMock->shouldReceive('whereIn')->never();
        $this->queryMock->shouldReceive('where')
            ->once()
            ->with('status', '=', Status::CLOSED->value)
            ->andReturnSelf();

        $this->qb->withStatus($request);
    }

    /**
     * @test
     * @covers ::withStatus
     */
    public function should_add_where_clause_with_status_open(): void
    {
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('input')
            ->once()
            ->with('withClosed')
            ->andReturnNull();

        $request->shouldReceive('input')
            ->once()
            ->with('onlyClosed')
            ->andReturn(0);

        $this->queryMock->shouldReceive('whereIn')->never();
        $this->queryMock->shouldReceive('where')
            ->once()
            ->with('status', '=', Status::OPEN->value)
            ->andReturnSelf();

        $this->qb->withStatus($request);
    }

    /**
     * @test
     * @covers ::withSorting
     * @dataProvider sortingDataProvider
     */
    public function should_handle_correctly_all_sorting_possible_cases(
        string $sortBy,
        string $expectedColumn,
        ?string $expectedDirection = null
    ): void {
        $request = \Mockery::mock(Request::class);

        $request->shouldReceive('input')
            ->with('sortBy')
            ->andReturn($sortBy);

        if (!is_null($expectedDirection)) {
            $this->queryMock->shouldReceive('orderBy')->once()->with($expectedColumn, $expectedDirection);
        } else {
            $this->queryMock->shouldReceive('orderBy')->once()->with($expectedColumn);
        }

        $this->qb->withSorting($request);
    }

    /**
     * Data provider for sorting test cases.
     *
     * @return array
     */
    public static function sortingDataProvider(): array
    {
        return [
            [SortingValues::ALPHADESC->value, 'title', 'desc'],
            [SortingValues::ALPHAASC->value, 'title', null],
            [SortingValues::UPDATE->value, 'updated_at', null],
            [SortingValues::CREATE->value, 'created_at', null],
            ['fake_wrong_value', 'created_at', null],
        ];
    }
}
