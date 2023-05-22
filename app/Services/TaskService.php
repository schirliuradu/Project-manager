<?php

namespace App\Services;

use App\Exceptions\ProjectNotFoundException;
use App\Exceptions\TaskNotFoundException;
use App\Http\Requests\AddTaskToProjectRequest;
use App\Http\Requests\GetProjectTaskRequest;
use App\Http\Requests\GetProjectTasksRequest;
use App\Http\Requests\UpdateProjectTaskRequest;
use App\Models\Enums\Status;
use App\Models\Enums\StatusActions;
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
     * @throws ProjectNotFoundException
     */
    public function getProjectTasks(GetProjectTasksRequest $request, string $id): array
    {
        // project id validation to check either exists or not
        // throws auto exception and blocks the flow
        $this->projectRepo->find($id);

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
        return [
            'data' => $this->repo
                ->getProjectTask($projectId, $taskId)
                ->toArray()
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
            $this->badRequestException();
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

        // new assignee validation
        if ($newAssignee = $request->input('assignee')) {
            if (!$this->userRepo->find($newAssignee)) {
                $this->badRequestException();
            }
        }

        return [
            'data' => $this->repo
                ->updateProjectTask($request, $task)
                ->toArray()
        ];
    }

    /**
     * @param string $projectId
     * @param string $taskId
     * @param string $action
     *
     * @return void
     * @throws ProjectNotFoundException
     * @throws TaskNotFoundException
     */
    public function updateProjectTaskStatus(string $projectId, string $taskId, string $action): void
    {
        $project = $this->projectRepo->find($projectId);

        if ($project->getAttribute('status') === Status::CLOSED->value) {
            $this->badRequestException();
        }

        $task = $this->repo->getProjectTask($projectId, $taskId);

        switch ($action) {
            case StatusActions::OPEN->value:
                $this->repo->openTask($task);
                break;

            case StatusActions::BLOCK->value:
                $this->repo->blockTask($task);
                break;

            case StatusActions::CLOSE->value:
                $this->repo->closeTask($task);
                break;

            default:
                break;
        }
    }

    /**
     * @param string $project
     * @param string $task
     * @param string $type
     *
     * @return void
     * @throws TaskNotFoundException|ProjectNotFoundException|HttpException
     */
    public function deleteProjectTask(string $project, string $task, string $type): void
    {
        $project = $this->projectRepo->find($project);

        // if project is closed we can't do stuff on it
        if ($project->getAttribute('status') === Status::CLOSED->value) {
            $this->badRequestException();
        }

        $this->repo->deleteProjectTask($task, $type);
    }

    /**
     * @return void
     * @throws HttpException
     */
    private function badRequestException(): void
    {
        throw new HttpException(400, 'Bad Request');
    }
}