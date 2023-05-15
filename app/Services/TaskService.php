<?php

namespace App\Services;

use App\Exceptions\ProjectNotFoundException;
use App\Http\Requests\AddTaskToProjectRequest;
use App\Http\Requests\GetProjectTasksRequest;
use App\Models\Enums\Status;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;
use App\Repositories\UserRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TaskService
{
    /**
     * @param TaskRepository $repo
     * @param ProjectRepository $projectRepo
     * @param UserRepository $userRepo
     */
    public function __construct(
        protected TaskRepository $repo,
        protected ProjectRepository $projectRepo,
        protected UserRepository $userRepo
    ) {
    }


    /**
     * @param GetProjectTasksRequest $request
     * @param string $id
     *
     * @return array
     */
    public function getProjectTasks(GetProjectTasksRequest $request, string $id): array
    {
        [$data, $meta] = $this->repo->searchProjectTasks($request, $id);

        return [
            'data' => $data,
            'meta' => $meta
        ];
    }

    /**
     * @param AddTaskToProjectRequest $request
     * @param string $projectId
     *
     * @return array
     * @throws ProjectNotFoundException
     */
    public function addTaskToProject(AddTaskToProjectRequest $request, string $projectId): array
    {
        $project = $this->projectRepo->find($projectId);
        $assignee = $this->userRepo->find($request->input('assignee'));

        if (
            !$assignee ||
            $project->getAttribute('status') === Status::CLOSED->value
        ) {
            throw new HttpException(400, 'Bad Request');
        }

        return $this->repo
            ->addTaskToProject($request, $projectId)
            ->toArray();
    }
}