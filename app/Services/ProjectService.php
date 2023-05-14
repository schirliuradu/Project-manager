<?php

namespace App\Services;

use App\Exceptions\ProjectNotFoundException;
use App\Http\Requests\AddProjectRequest;
use App\Http\Requests\GetProjectsRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Repositories\ProjectRepository;

class ProjectService
{
    /**
     * @param ProjectRepository $repo
     */
    public function __construct(protected ProjectRepository $repo)
    {
    }

    /**
     * @param GetProjectsRequest $request
     *
     * @return array
     */
    public function getProjects(GetProjectsRequest $request): array
    {
        [$data, $meta] = $this->repo->searchProjects($request);

        return [
            'data' => $data,
            'meta' => $meta
        ];
    }

    /**
     * @param string $project
     *
     * @return array
     * @throws ProjectNotFoundException
     */
    public function getProject(string $project): array
    {
        return [
            'data' => $this->repo
                ->find($project)
                ->toArray()
        ];
    }

    /**
     * @param AddProjectRequest $request
     *
     * @return array
     */
    public function addProject(AddProjectRequest $request): array
    {
        return ['data' => $this->repo->addProject($request)];
    }

    /**
     * @param UpdateProjectRequest $request
     * @param string $id
     *
     * @return array
     * @throws ProjectNotFoundException
     */
    public function updateProject(UpdateProjectRequest $request, string $id): array
    {
        $project = $this->repo->find($id);

        return [
            'data' => $this->repo
                ->updateProject($project, $request)
                ->toArray()
        ];
    }
}