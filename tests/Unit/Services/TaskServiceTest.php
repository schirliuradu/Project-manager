<?php

namespace Tests\Unit\Services;

use App\Exceptions\ProjectNotFoundException;
use App\Http\Requests\AddTaskToProjectRequest;
use App\Http\Requests\GetProjectTasksRequest;
use App\Models\Enums\Status;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;
use App\Repositories\UserRepository;
use App\Services\TaskService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @coversDefaultClass \App\Services\TaskService
 */
class TaskServiceTest extends TestCase
{
    private TaskService $service;

    private TaskRepository $taskRepository;
    private ProjectRepository $projectRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taskRepository = \Mockery::mock(TaskRepository::class);
        $this->projectRepository = \Mockery::mock(ProjectRepository::class);
        $this->userRepository = \Mockery::mock(UserRepository::class);

        $this->service = new TaskService(
            $this->taskRepository,
            $this->projectRepository,
            $this->userRepository
        );
    }

    /**
     * Tell mockery to count everything as assertion, to test flows and avoid risky tests
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        \Mockery::close();
    }

    /**
     * @test
     * @covers ::getProjectTasks
     */
    public function get_project_tasks_should_first_validate_given_project_id_and_throw_exception_if_not_found(): void
    {
        $this->projectRepository->shouldReceive('find')
            ->once()
            ->andThrow(ProjectNotFoundException::class);

        $this->expectException(ProjectNotFoundException::class);
        $this->service->getProjectTasks(\Mockery::mock(GetProjectTasksRequest::class), 'fake_uuid');
    }

    /**
     * @test
     * @covers ::getProjectTasks
     */
    public function get_project_tasks_should_return_wrapped_repo_returned_results(): void
    {
        $fakeData = ['fake_data'];
        $fakeMeta = ['fake_meta'];

        $fakeRequestMock = \Mockery::mock(GetProjectTasksRequest::class);
        $this->projectRepository->shouldReceive('find')
            ->once()
            ->andReturn(\Mockery::mock(Project::class));

        $this->taskRepository->shouldReceive('searchProjectTasks')
            ->once()
            ->with($fakeRequestMock, 'fake_uuid')
            ->andReturn([$fakeData, $fakeMeta]);

        $this->assertEquals([
            'data' => $fakeData,
            'meta' => $fakeMeta
        ], $this->service->getProjectTasks($fakeRequestMock, 'fake_uuid'));
    }

    /**
     * @test
     * @covers ::addTaskToProject
     */
    public function add_task_to_project_should_throw_custom_exception_if_given_project_does_not_exist()
    {
        $this->projectRepository->shouldReceive('find')
            ->with('fake_uuid')
            ->once()
            ->andThrow(ProjectNotFoundException::class);

        $this->expectException(ProjectNotFoundException::class);
        $this->service->addTaskToProject(\Mockery::mock(AddTaskToProjectRequest::class), 'fake_uuid');
    }

    /**
     * @test
     * @covers ::addTaskToProject
     */
    public function add_task_to_project_should_throw_bad_request_if_assignee_does_not_exist()
    {
        $fakeProjectMock = \Mockery::mock(Project::class);
//        $fakeProjectMock->shouldReceive('getAttribute')
////            ->once()
//            ->with('status')
//            ->andReturn(Status::OPEN->value);

        $fakeRequestMock = \Mockery::mock(AddTaskToProjectRequest::class);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('assignee')
            ->andReturn('fake_user_uuid');

        $this->projectRepository->shouldReceive('find')
            ->with('fake_uuid')
            ->once()
            ->andReturn($fakeProjectMock);

        $this->userRepository->shouldReceive('find')
            ->once()
            ->with('fake_user_uuid')
            ->andReturnNull();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Bad Request');

        $this->service->addTaskToProject($fakeRequestMock, 'fake_uuid');
    }

    /**
     * @test
     * @covers ::addTaskToProject
     */
    public function add_task_to_project_should_throw_bad_request_if_project_status_is_closed()
    {
        $fakeProjectMock = \Mockery::mock(Project::class);
        $fakeProjectMock->shouldReceive('getAttribute')
            ->once()
            ->with('status')
            ->andReturn(Status::CLOSED->value);

        $fakeRequestMock = \Mockery::mock(AddTaskToProjectRequest::class);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('assignee')
            ->andReturn('fake_user_uuid');

        $this->projectRepository->shouldReceive('find')
            ->with('fake_uuid')
            ->once()
            ->andReturn($fakeProjectMock);

        $this->userRepository->shouldReceive('find')
            ->once()
            ->with('fake_user_uuid')
            ->andReturn(\Mockery::mock(User::class));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Bad Request');

        $this->service->addTaskToProject($fakeRequestMock, 'fake_uuid');
    }

    /**
     * @test
     * @covers ::addTaskToProject
     */
    public function add_task_to_project_should_perform_action_correctly_and_return_new_task_as_array_wrapped()
    {
        $fakeProjectMock = \Mockery::mock(Project::class);
        $fakeProjectMock->shouldReceive('getAttribute')
            ->once()
            ->with('status')
            ->andReturn(Status::OPEN->value);

        $fakeTaskMock = \Mockery::mock(Task::class);
        $fakeTaskMock->shouldReceive('toArray')
            ->once()
            ->andReturn(['fake_task_to_array']);

        $fakeRequestMock = \Mockery::mock(AddTaskToProjectRequest::class);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('assignee')
            ->andReturn('fake_user_uuid');

        $this->projectRepository->shouldReceive('find')
            ->with('fake_uuid')
            ->once()
            ->andReturn($fakeProjectMock);

        $this->userRepository->shouldReceive('find')
            ->once()
            ->with('fake_user_uuid')
            ->andReturn(\Mockery::mock(User::class));

        $this->taskRepository->shouldReceive('addTaskToProject')
            ->once()
            ->with($fakeRequestMock, 'fake_uuid')
            ->andReturn($fakeTaskMock);

        $this->assertEquals([
            'data' => ['fake_task_to_array']
        ], $this->service->addTaskToProject($fakeRequestMock, 'fake_uuid'));
    }
}
