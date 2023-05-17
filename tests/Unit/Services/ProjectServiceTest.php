<?php

namespace Tests\Unit\Services;

use App\Http\Requests\GetProjectsRequest;
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
}
