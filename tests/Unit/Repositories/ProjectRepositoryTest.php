<?php

namespace Tests\Unit\Repositories;

use App\Factories\SearchQueryBuilderFactory;
use App\Helpers\Formatters\PaginationFormatter;
use App\Http\Requests\GetProjectsRequest;
use App\Models\Project;
use App\Repositories\Builders\SearchQueryBuilder;
use App\Repositories\ProjectRepository;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Repositories\ProjectRepository
 */
class ProjectRepositoryTest extends TestCase
{
    private Project $modelMock;
    private PaginationFormatter $paginationFormatterMock;
    private ProjectFactory $projectFactoryMock;
    private SearchQueryBuilderFactory $builderFactoryMock;

    private ProjectRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modelMock = \Mockery::mock(Project::class);
        $this->paginationFormatterMock = \Mockery::mock(PaginationFormatter::class);
        $this->projectFactoryMock = \Mockery::mock(ProjectFactory::class);
        $this->builderFactoryMock = \Mockery::mock(SearchQueryBuilderFactory::class);

        $this->repo = new ProjectRepository(
            $this->modelMock,
            $this->paginationFormatterMock,
            $this->projectFactoryMock,
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

        $requestMock = \Mockery::mock(GetProjectsRequest::class);
        $requestMock->shouldReceive('input')->with('perPage')->andReturn(10);
        $requestMock->shouldReceive('input')->with('page')->andReturn(3);

        $builderMock = \Mockery::mock(SearchQueryBuilder::class);

        $this->builderFactoryMock->shouldReceive('create')
            ->once()
            ->andReturn($builderMock);

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
        ], $this->repo->searchProjects($requestMock));
    }
}
