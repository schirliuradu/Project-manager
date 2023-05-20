<?php

namespace Tests\Unit\Repositories;

use App\Factories\SearchQueryBuilderFactory;
use App\Helpers\Formatters\PaginationFormatter;
use App\Http\Requests\GetProjectTasksRequest;
use App\Models\Task;
use App\Repositories\Builders\SearchQueryBuilder;
use App\Repositories\TaskRepository;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\Unit\UnitTestCase;

/**
 * @coversDefaultClass \App\Repositories\TaskRepository
 */
class TaskRepositoryTest extends UnitTestCase
{
    private TaskRepository $repo;

    private Task $modelMock;
    private PaginationFormatter $paginationFormatterMock;
    private TaskFactory $taskFactoryMock;
    private SearchQueryBuilderFactory $builderFactoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modelMock = \Mockery::mock(Task::class);
        $this->paginationFormatterMock = \Mockery::mock(PaginationFormatter::class);
        $this->taskFactoryMock = \Mockery::mock(TaskFactory::class);
        $this->builderFactoryMock = \Mockery::mock(SearchQueryBuilderFactory::class);

        $this->repo = new TaskRepository(
            $this->modelMock,
            $this->paginationFormatterMock,
            $this->taskFactoryMock,
            $this->builderFactoryMock
        );
    }

    /**
     * @test
     * @covers ::searchProjects
     */
    public function should_use_search_query_builder_module_paginate_and_return_data_and_pagination_information(): void
    {
        $queryMock = \Mockery::mock(Builder::class);
        $this->modelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $requestMock = \Mockery::mock(GetProjectTasksRequest::class);
        $requestMock->shouldReceive('input')->with('perPage')->andReturn(10);
        $requestMock->shouldReceive('input')->with('page')->andReturn(3);

        $builderMock = \Mockery::mock(SearchQueryBuilder::class);

        $this->builderFactoryMock->shouldReceive('create')
            ->once()
            ->andReturn($builderMock);

        $builderMock->shouldReceive('withProject')
            ->once()
            ->with('fake_uuid')
            ->andReturnSelf();

        $builderMock->shouldReceive('withStatus')
            ->once()
            ->with($requestMock)
            ->andReturnSelf();

        $builderMock->shouldReceive('withSorting')
            ->once()
            ->with($requestMock)
            ->andReturnSelf();

        $paginatorMock = \Mockery::mock(LengthAwarePaginator::class);
        $paginatorMock->shouldReceive('items')
            ->once()
            ->andReturn(['fake_items']);

        $queryMock->shouldReceive('paginate')
            ->once()
            ->with(10, '*', 'page', 3)
            ->andReturn($paginatorMock);

        $this->paginationFormatterMock->shouldReceive('format')
            ->once()
            ->with($paginatorMock)
            ->andReturn(['fake_formatted_pagination_array']);

        $this->assertEquals([
            ['fake_items'],
            ['fake_formatted_pagination_array'],
        ], $this->repo->searchProjectTasks($requestMock, 'fake_uuid'));
    }
}
