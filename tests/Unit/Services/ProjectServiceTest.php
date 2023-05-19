<?php

namespace Tests\Unit\Services;

use App\Exceptions\ProjectNotFoundException;
use App\Http\Requests\GetProjectsRequest;
use App\Models\Project;
use App\Repositories\ProjectRepository;
use App\Services\ProjectService;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Services\ProjectService
 */
class ProjectServiceTest extends TestCase
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
}
