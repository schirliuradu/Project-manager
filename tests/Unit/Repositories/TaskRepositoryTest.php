<?php

namespace Tests\Unit\Repositories;

use App\Exceptions\TaskNotFoundException;
use App\Factories\SearchQueryBuilderFactory;
use App\Helpers\Formatters\PaginationFormatter;
use App\Http\Requests\AddTaskToProjectRequest;
use App\Http\Requests\GetProjectTasksRequest;
use App\Http\Requests\UpdateProjectTaskRequest;
use App\Models\Enums\Difficulty;
use App\Models\Enums\Priority;
use App\Models\Enums\Status;
use App\Models\Task;
use App\Repositories\Builders\SearchQueryBuilder;
use App\Repositories\TaskRepository;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Mockery;
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

        $this->modelMock = Mockery::mock(Task::class);
        $this->paginationFormatterMock = Mockery::mock(PaginationFormatter::class);
        $this->taskFactoryMock = Mockery::mock(TaskFactory::class);
        $this->builderFactoryMock = Mockery::mock(SearchQueryBuilderFactory::class);

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
        $queryMock = Mockery::mock(Builder::class);
        $this->modelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $requestMock = Mockery::mock(GetProjectTasksRequest::class);
        $requestMock->shouldReceive('input')->with('perPage')->andReturn(10);
        $requestMock->shouldReceive('input')->with('page')->andReturn(3);

        $builderMock = Mockery::mock(SearchQueryBuilder::class);

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

        $paginatorMock = Mockery::mock(LengthAwarePaginator::class);
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

    /**
     * @test
     * @covers ::addTaskToProject
     */
    public function add_task_to_project_should_create_new_task_and_return_it()
    {
        $fakeTaskMock = Mockery::mock(Task::class);
        $fakeTaskMock->shouldReceive('save')
            ->once()
            ->andReturnSelf();
        $fakeTaskMock->shouldReceive('getAttribute')
            ->once()
            ->with('id')
            ->andReturn($id = Str::uuid()->toString());
        $fakeTaskMock->shouldReceive('setAttribute')
            ->once()
            ->with('slug', $id . '-lorem-ipsum-title')
            ->andReturnSelf();

        $fakeRequestMock = Mockery::mock(AddTaskToProjectRequest::class);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('title')
            ->andReturn($title = 'lorem ipsum title');
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('assignee')
            ->andReturn($assignee = Str::uuid()->toString());
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('description')
            ->andReturn($description = 'lorem ipsum description');
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('difficulty')
            ->andReturn($difficulty = Difficulty::FIVE->value);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('priority')
            ->andReturn($priority = Priority::HIGH->value);

        $this->taskFactoryMock->shouldReceive('create')
            ->once()
            ->with([
                'project_id' => 'fake_uuid',
                'assignee_id' => $assignee,
                'title' => $title,
                'description' => $description,
                'difficulty' => $difficulty,
                'priority' => $priority,
                'status' => Status::OPEN->value
            ])->andReturn($fakeTaskMock);

        $this->assertEquals(
            $fakeTaskMock,
            $this->repo->addTaskToProject($fakeRequestMock, 'fake_uuid')
        );
    }

    /**
     * @test
     * @covers ::getProjectTask
     */
    public function get_project_task_should_throw_custom_exception_if_no_task_found_for_given_id()
    {
        $queryMock = Mockery::mock(Builder::class);
        $queryMock->shouldReceive('where')
            ->once()
            ->with('id', '=', $fakeTaskId = 'fake_task_uuid')
            ->andReturnSelf();
        $queryMock->shouldReceive('where')
            ->once()
            ->with('project_id', '=', $fakeProjectId = 'fake_project_uuid')
            ->andReturnSelf();
        $queryMock->shouldReceive('first')
            ->once()
            ->andReturnNull();

        $this->modelMock->shouldReceive('query')
            ->once()
            ->andReturn($queryMock);

        $this->expectException(TaskNotFoundException::class);
        $this->repo->getProjectTask($fakeProjectId, $fakeTaskId);
    }

    /**
     * @test
     * @covers ::getProjectTask
     */
    public function get_project_task_should_return_found_task_instance()
    {
        $queryMock = Mockery::mock(Builder::class);
        $queryMock->shouldReceive('where')
            ->once()
            ->with('id', '=', $fakeTaskId = 'fake_task_uuid')
            ->andReturnSelf();
        $queryMock->shouldReceive('where')
            ->once()
            ->with('project_id', '=', $fakeProjectId = 'fake_project_uuid')
            ->andReturnSelf();
        $queryMock->shouldReceive('first')
            ->once()
            ->andReturn($this->modelMock);

        $this->modelMock->shouldReceive('query')
            ->once()
            ->andReturn($queryMock);

        $this->assertInstanceOf(Task::class, $this->repo->getProjectTask($fakeProjectId, $fakeTaskId));
    }

    /**
     * @test
     * @covers ::updateProjectTask
     * @dataProvider updateProjectTaskDataProvider
     */
    public function update_project_task_should_update_params_only_if_they_are_set_on_request_object(
        string $param,
        string $value
    ): void {
        $fakeRequestMock = Mockery::mock(UpdateProjectTaskRequest::class);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with($param)
            ->andReturn($value);

        $fakeRequestMock->shouldReceive('input')
            ->times(4)
            ->andReturnNull();

        $fakeTaskMock = Mockery::mock(Task::class);
        $fakeTaskMock->shouldReceive('setAttribute')
            ->once()
            ->with($param, $value)
            ->andReturnSelf();

        $fakeTaskMock->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->assertInstanceOf(Task::class, $this->repo->updateProjectTask($fakeRequestMock, $fakeTaskMock));
    }

    /**
     * Data provider for update project task test cases.
     *
     * @return array
     */
    public static function updateProjectTaskDataProvider(): array
    {
        return [
            ['title', 'lorem title'],
            ['description', 'lorem description'],
            ['assignee', 'lorem assignee'],
            ['difficulty', 'lorem difficulty'],
            ['priority', 'lorem priority']
        ];
    }
}
