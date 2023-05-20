<?php

namespace Tests\Unit\Services;

use App\Exceptions\ProjectNotFoundException;
use App\Http\Requests\GetProjectTasksRequest;
use App\Models\Project;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;
use App\Repositories\UserRepository;
use App\Services\TaskService;
use PHPUnit\Framework\TestCase;

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
}
