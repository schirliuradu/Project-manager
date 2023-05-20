<?php

namespace Tests\Unit\Services;

use App\Exceptions\ProjectNotFoundException;
use App\Http\Requests\AddProjectRequest;
use App\Http\Requests\GetProjectsRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Enums\Status;
use App\Models\Project;
use App\Repositories\ProjectRepository;
use App\Services\ProjectService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Unit\UnitTestCase;

/**
 * @coversDefaultClass \App\Services\ProjectService
 */
class ProjectServiceTest extends UnitTestCase
{
    private ProjectRepository $projectRepositoryMock;
    private ProjectService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectRepositoryMock = \Mockery::mock(ProjectRepository::class);
        $this->service = new ProjectService($this->projectRepositoryMock);
    }

    /**
     * @test
     * @covers ::getProjects
     */
    public function get_projects_should_return_data_returned_from_repository_with_custom_keys(): void
    {
        $fakeData = ['fake_data'];
        $fakeMeta = ['fake_meta'];

        $requestMock = \Mockery::mock(GetProjectsRequest::class);
        $this->projectRepositoryMock->shouldReceive('searchProjects')
            ->once()
            ->with($requestMock)
            ->andReturn([$fakeData, $fakeMeta]);

        $this->assertEquals([
            'data' => $fakeData,
            'meta' => $fakeMeta
        ], $this->service->getProjects($requestMock));
    }

    /**
     * @test
     * @covers ::getProject
     */
    public function get_project_should_return_data_returned_from_repository_with_custom_keys(): void
    {
        $fakeProjectArray = ['fake_project'];
        $fakeProjectMock = \Mockery::mock(Project::class);
        $fakeProjectMock->shouldReceive('toArray')
            ->once()
            ->andReturn($fakeProjectArray);

        $this->projectRepositoryMock->shouldReceive('find')
            ->once()
            ->with('fake_uuid')
            ->andReturn($fakeProjectMock);

        $this->assertEquals([
            'data' => $fakeProjectArray
        ], $this->service->getProject('fake_uuid'));
    }

    /**
     * @test
     * @covers ::getProject
     */
    public function get_project_should_bubble_up_repository_thrown_exceptions(): void
    {
        $this->projectRepositoryMock->shouldReceive('find')
            ->once()
            ->with('fake_uuid')
            ->andThrow(ProjectNotFoundException::class);

        $this->expectException(ProjectNotFoundException::class);

        $this->service->getProject('fake_uuid');
    }

    /**
     * @test
     * @covers ::addProject
     */
    public function add_project_should_insert_new_post_and_return_data_returned_post_as_array(): void
    {
        $fakeRequestMock = \Mockery::mock(AddProjectRequest::class);

        $fakeProjectArray = ['fake_project'];
        $fakeProjectMock = \Mockery::mock(Project::class);
        $fakeProjectMock->shouldReceive('toArray')
            ->once()
            ->andReturn($fakeProjectArray);

        $this->projectRepositoryMock->shouldReceive('addProject')
            ->once()
            ->with($fakeRequestMock)
            ->andReturn($fakeProjectMock);

        $this->assertEquals([
            'data' => $fakeProjectArray
        ], $this->service->addProject($fakeRequestMock));
    }

    /**
     * @test
     * @covers ::updateProject
     */
    public function update_project_should_bubble_up_project_not_found_exception_if_thrown()
    {
        $this->projectRepositoryMock->shouldReceive('find')
            ->once()
            ->with('fake_uuid')
            ->andThrow(ProjectNotFoundException::class);

        $this->expectException(ProjectNotFoundException::class);
        $this->service->updateProject(\Mockery::mock(UpdateProjectRequest::class), 'fake_uuid');
    }

    /**
     * @test
     * @covers ::updateProject
     */
    public function update_project_should_return_updated_project_as_array()
    {
        $fakeProjectMock = \Mockery::mock(Project::class);
        $fakeRequestMock = \Mockery::mock(UpdateProjectRequest::class);
        $fakeProjectArray = ['fake_project'];

        $this->projectRepositoryMock->shouldReceive('find')
            ->once()
            ->with('fake_uuid')
            ->andReturn($fakeProjectMock);

        $this->projectRepositoryMock->shouldReceive('updateProject')
            ->once()
            ->with($fakeProjectMock, $fakeRequestMock)
            ->andReturn($fakeProjectMock);

        $fakeProjectMock->shouldReceive('toArray')
            ->once()
            ->andReturn($fakeProjectArray);

        $this->assertEquals([
            'data' => $fakeProjectArray
        ], $this->service->updateProject($fakeRequestMock, 'fake_uuid'));
    }

    /**
     * @test
     * @covers ::updateProjectStatus
     */
    public function update_project_status_should_bubble_up_project_not_found_exception()
    {
        $this->projectRepositoryMock->shouldReceive('find')
            ->once()
            ->with('fake_uuid')
            ->andThrow(ProjectNotFoundException::class);

        $this->expectException(ProjectNotFoundException::class);
        $this->service->updateProjectStatus('fake_uuid', Status::OPEN->value);
    }

    /**
     * @test
     * @covers ::updateProjectStatus
     */
    public function update_project_status_should_throw_bad_request_exception_if_try_to_open_closed_project()
    {
        $fakeProjectMock = \Mockery::mock(Project::class);
        $fakeProjectMock->shouldReceive('getAttribute')
            ->once()
            ->andReturn(Status::CLOSED->value);

        $this->projectRepositoryMock->shouldReceive('find')
            ->once()
            ->with('fake_uuid')
            ->andReturn($fakeProjectMock);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Bad Request');

        $this->service->updateProjectStatus('fake_uuid', Status::OPEN->value);
    }

    /**
     * @test
     * @covers ::updateProjectStatus
     */
    public function update_project_status_should_open_project_correctly()
    {
        $fakeProjectMock = \Mockery::mock(Project::class);
        $fakeProjectMock->shouldReceive('getAttribute')
            ->once()
            ->andReturn(Status::OPEN->value);

        $this->projectRepositoryMock->shouldReceive('find')
            ->once()
            ->with('fake_uuid')
            ->andReturn($fakeProjectMock);

        $this->projectRepositoryMock->shouldReceive('openProject')
            ->once()
            ->with($fakeProjectMock);

        $this->service->updateProjectStatus('fake_uuid', Status::OPEN->value);
    }

    /**
     * @test
     * @covers ::updateProjectStatus
     */
    public function update_project_status_should_throw_bad_request_exception_if_project_still_has_opened_tasks()
    {
        $fakeProjectMock = \Mockery::mock(Project::class);

        $this->projectRepositoryMock->shouldReceive('find')
            ->once()
            ->with('fake_uuid')
            ->andReturn($fakeProjectMock);

        $this->projectRepositoryMock->shouldReceive('hasOpenedTasks')
            ->once()
            ->andReturnTrue();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Bad Request');

        $this->service->updateProjectStatus('fake_uuid', Status::CLOSED->value);
    }

    /**
     * @test
     * @covers ::updateProjectStatus
     */
    public function update_project_status_should_close_project_correctly()
    {
        $fakeProjectMock = \Mockery::mock(Project::class);

        $this->projectRepositoryMock->shouldReceive('find')
            ->once()
            ->with('fake_uuid')
            ->andReturn($fakeProjectMock);

        $this->projectRepositoryMock->shouldReceive('closeProject')
            ->once()
            ->with($fakeProjectMock);

        $this->projectRepositoryMock->shouldReceive('hasOpenedTasks')
            ->once()
            ->andReturnFalse();

        $this->service->updateProjectStatus('fake_uuid', Status::CLOSED->value);
    }
}
