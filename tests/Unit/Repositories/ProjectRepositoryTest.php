<?php

namespace Tests\Unit\Repositories;

use App\Exceptions\ProjectNotFoundException;
use App\Factories\SearchQueryBuilderFactory;
use App\Helpers\Formatters\PaginationFormatter;
use App\Http\Requests\AddProjectRequest;
use App\Http\Requests\GetProjectsRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Enums\DeletionType;
use App\Models\Enums\Status;
use App\Models\Project;
use App\Repositories\Builders\SearchQueryBuilder;
use App\Repositories\ProjectRepository;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\Mock;
use Tests\Unit\UnitTestCase;

/**
 * @coversDefaultClass \App\Repositories\ProjectRepository
 */
class ProjectRepositoryTest extends UnitTestCase
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

    /**
     * @test
     * @covers ::find
     */
    public function should_return_found_project_if_there_is_one_for_given_id(): void
    {
        $fakeProjectMock = \Mockery::mock(Project::class);

        $queryMock = \Mockery::mock(Builder::class);
        $this->modelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $queryMock->shouldReceive('where')
            ->once()
            ->with('id', '=', 'fake_uuid')
            ->andReturnSelf();

        $queryMock->shouldReceive('first')
            ->once()
            ->andReturn($fakeProjectMock);

        $this->assertEquals($fakeProjectMock, $this->repo->find('fake_uuid'));
    }

    /**
     * @test
     * @covers ::find
     */
    public function should_throw_custom_exception_if_there_is_no_project_for_given_id(): void
    {
        $queryMock = \Mockery::mock(Builder::class);
        $this->modelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $queryMock->shouldReceive('where')
            ->once()
            ->with('id', '=', 'fake_uuid')
            ->andReturnSelf();

        $queryMock->shouldReceive('first')
            ->once()
            ->andThrow(ProjectNotFoundException::class);

        $this->expectException(ProjectNotFoundException::class);

        $this->repo->find('fake_uuid');
    }

    /**
     * @test
     * @covers ::addProject
     */
    public function add_project_should_create_new_project_model_and_save_it_with_correct_param_values(): void
    {
        $fakeRequestMock = \Mockery::mock(AddProjectRequest::class);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('title')
            ->andReturns('Lorem ipsum', );
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('description')
            ->andReturns('dolor sit amet');

        $fakeProjectMock = \Mockery::mock(Project::class);
        $fakeProjectMock->shouldReceive('getAttribute')
            ->once()
            ->with('id')
            ->andReturn('fake-uuid');
        $fakeProjectMock->shouldReceive('setAttribute')
            ->once()
            ->with('slug', 'fake-uuid-lorem-ipsum');
        $fakeProjectMock->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->projectFactoryMock->shouldReceive('create')
            ->once()
            ->with([
                'title' => 'Lorem ipsum',
                'description' => 'dolor sit amet',
                'status' => Status::OPEN->value
            ])->andReturn($fakeProjectMock);

        $this->assertEquals($fakeProjectMock, $this->repo->addProject($fakeRequestMock));
    }

    /**
     * @test
     * @covers ::updateProject
     */
    public function update_project_should_only_update_title_if_set_on_request()
    {
        $fakeProjectMock = \Mockery::mock(Project::class);
        $fakeRequestMock = \Mockery::mock(UpdateProjectRequest::class);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('title')
            ->andReturn('lorem ipsum updated');
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('description')
            ->andReturnNull();

        $fakeProjectMock->shouldReceive('setAttribute')
            ->once()
            ->with('title', 'lorem ipsum updated');

        $fakeProjectMock->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->repo->updateProject($fakeProjectMock, $fakeRequestMock);
    }

    /**
     * @test
     * @covers ::updateProject
     */
    public function update_project_should_only_update_description_if_set_on_request()
    {
        $fakeProjectMock = \Mockery::mock(Project::class);
        $fakeRequestMock = \Mockery::mock(UpdateProjectRequest::class);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('title')
            ->andReturnNull();
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('description')
            ->andReturn('lorem ipsum description updated');

        $fakeProjectMock->shouldReceive('setAttribute')
            ->once()
            ->with('description', 'lorem ipsum description updated');

        $fakeProjectMock->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->repo->updateProject($fakeProjectMock, $fakeRequestMock);
    }

    /**
     * @test
     * @covers ::openProject
     */
    public function open_project_should_set_status_property_to_open_correctly()
    {
        $fakeProjectMock = \Mockery::mock(Project::class);
        $fakeProjectMock->shouldReceive('setAttribute')
            ->once()
            ->with('status', Status::OPEN->value)
            ->andReturnSelf();
        $fakeProjectMock->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->assertEquals($fakeProjectMock, $this->repo->openProject($fakeProjectMock));
    }

    /**
     * @test
     * @covers ::closeProject
     */
    public function close_project_should_set_status_property_to_closed_correctly()
    {
        $fakeProjectMock = \Mockery::mock(Project::class);
        $fakeProjectMock->shouldReceive('setAttribute')
            ->once()
            ->with('status', Status::CLOSED->value)
            ->andReturnSelf();
        $fakeProjectMock->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->assertEquals($fakeProjectMock, $this->repo->closeProject($fakeProjectMock));
    }

    /**
     * @test
     * @covers ::hasOpenedTasks
     */
    public function has_opened_tasks_should_return_true_or_false()
    {
        $openedTasksMock = \Mockery::mock(HasMany::class);
        $openedTasksMock->shouldReceive('count')
            ->once()
            ->andReturn(3);

        $fakeProjectMock = \Mockery::mock(Project::class);
        $fakeProjectMock->shouldReceive('openedTasks')
            ->once()
            ->andReturn($openedTasksMock);

        $this->assertTrue($this->repo->hasOpenedTasks($fakeProjectMock));
    }

    /**
     * @test
     * @covers ::deleteProject
     */
    public function delete_project_should_bubble_up_custom_exceptions()
    {
        $repo = \Mockery::mock(ProjectRepository::class, [
            $this->modelMock,
            $this->paginationFormatterMock,
            $this->projectFactoryMock,
            $this->builderFactoryMock
        ])->makePartial();

        $repo->shouldReceive('findWithTrashed')
            ->once()
            ->with($fakeProjectId = 'fake_project_uuid')
            ->andThrow(ProjectNotFoundException::class);

        $this->expectException(ProjectNotFoundException::class);
        $repo->deleteProject($fakeProjectId, DeletionType::SOFT->value);
    }

    /**
     * @test
     * @covers ::deleteProject
     */
    public function delete_project_should_soft_delete_project()
    {
        $repo = \Mockery::mock(ProjectRepository::class, [
            $this->modelMock,
            $this->paginationFormatterMock,
            $this->projectFactoryMock,
            $this->builderFactoryMock
        ])->makePartial();

        $fakeProjectMock = \Mockery::mock(Project::class);
        $fakeProjectMock->shouldReceive('delete')
            ->once();
        $fakeProjectMock->shouldReceive('forceDelete')
            ->never();

        $repo->shouldReceive('findWithTrashed')
            ->once()
            ->with($fakeProjectId = 'fake_project_uuid')
            ->andReturn($fakeProjectMock);

        $repo->deleteProject($fakeProjectId, DeletionType::SOFT->value);
    }

    /**
     * @test
     * @covers ::deleteProject
     */
    public function delete_project_should_hard_delete_project()
    {
        $repo = \Mockery::mock(ProjectRepository::class, [
            $this->modelMock,
            $this->paginationFormatterMock,
            $this->projectFactoryMock,
            $this->builderFactoryMock
        ])->makePartial();

        $fakeProjectMock = \Mockery::mock(Project::class);
        $fakeProjectMock->shouldReceive('forceDelete')
            ->once();
        $fakeProjectMock->shouldReceive('delete')
            ->never();

        $repo->shouldReceive('findWithTrashed')
            ->once()
            ->with($fakeProjectId = 'fake_project_uuid')
            ->andReturn($fakeProjectMock);

        $repo->deleteProject($fakeProjectId, DeletionType::HARD->value);
    }

    /**
     * @test
     * @covers ::findWithTrashed
     */
    public function find_with_trashed_should_throw_custom_exception_if_project_not_found_for_given_id(): void
    {
        $queryMock = \Mockery::mock(Builder::class);
        $queryMock->shouldReceive('find')
            ->once()
            ->with($fakeProjectId = 'fake_project_uuid')
            ->andThrow(ProjectNotFoundException::class);

        $this->modelMock->shouldReceive('newQueryWithoutScopes')
            ->once()
            ->andReturn($queryMock);

        $this->expectException(ProjectNotFoundException::class);
        $this->repo->findWithTrashed($fakeProjectId);
    }

    /**
     * @test
     * @covers ::findWithTrashed
     */
    public function find_with_trashed_should_return_project_instance_when_found(): void
    {
        $queryMock = \Mockery::mock(Builder::class);
        $queryMock->shouldReceive('find')
            ->once()
            ->with($fakeProjectId = 'fake_project_uuid')
            ->andReturn(\Mockery::mock(Project::class));

        $this->modelMock->shouldReceive('newQueryWithoutScopes')
            ->once()
            ->andReturn($queryMock);

        $this->assertInstanceOf(Project::class, $this->repo->findWithTrashed($fakeProjectId));
    }
}
