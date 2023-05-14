<?php

namespace App\Services;

use App\Exceptions\ProjectNotFoundException;
use App\Http\Requests\AddProjectRequest;
use App\Http\Requests\GetProjectsRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Enums\Status;
use App\Models\Enums\StatusActions;
use App\Repositories\ProjectRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    /**
     * @param string $id
     * @param string $status
     *
     * @return void
     * @throws ProjectNotFoundException
     * @throws HttpException
     */
    public function updateProjectStatus(string $id, string $status): void
    {
        $project = $this->repo->find($id);

        if ($status === StatusActions::OPEN->value) {
            // open action with closed project -> boom
            if ($project->getAttribute('status') === Status::CLOSED->value) {
                throw new HttpException(400, 'Bad Request');
            }

            // open project in all other cases
            // in fact, we can open just already opened projects: does that make sense?
            $this->repo->openProject($project);
        } else {
            if ($this->repo->hasOpenedTasks($project)) {
                // close action but project still has some opened tasks
                throw new HttpException(400, 'Bad Request');
            }

            $this->repo->closeProject($project);
        }
    }
}