<?php

namespace App\Services;

use App\Exceptions\ProjectNotFoundException;
use App\Exceptions\TaskNotFoundException;
use App\Http\Requests\AddTaskToProjectRequest;
use App\Http\Requests\GetProjectTaskRequest;
use App\Http\Requests\GetProjectTasksRequest;
use App\Http\Requests\UpdateProjectTaskRequest;
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
     * @param GetProjectTaskRequest $request
     * @param string $projectId
     * @param string $taskId
     *
     * @return array
     * @throws TaskNotFoundException
     */
    public function getProjectTask(GetProjectTaskRequest $request, string $projectId, string $taskId): array
    {
        $task = $this->repo->getProjectTask($projectId, $taskId);

        if (!$task) {
            throw new TaskNotFoundException();
        }

        return [
            'data' => $task->toArray()
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

        return [
            'data' => $this->repo
                ->addTaskToProject($request, $projectId)
                ->toArray()
        ];
    }

    /**
     * @param UpdateProjectTaskRequest $request
     * @param string $projectId
     * @param string $taskId
     *
     * @return array[]
     * @throws TaskNotFoundException|ProjectNotFoundException
     */
    public function updateProjectTask(UpdateProjectTaskRequest $request, string $projectId, string $taskId): array
    {
        // project validation
        $this->projectRepo->find($projectId);

        $task = $this->repo->getProjectTask($projectId, $taskId);

        if (!$task) {
            throw new TaskNotFoundException();
        }

        // new assignee validation
        if ($newAssignee = $request->input('assignee')) {
            if (!$this->userRepo->find($newAssignee)) {
                throw new HttpException(400, 'Bad Request');
            }
        }

        return [
            'data' => $this->repo
                ->updateProjectTask($request, $task)
                ->toArray()
        ];
    }
}