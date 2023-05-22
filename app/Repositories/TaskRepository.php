<?php

namespace App\Repositories;

use App\Exceptions\ProjectNotFoundException;
use App\Exceptions\TaskNotFoundException;
use App\Factories\SearchQueryBuilderFactory;
use App\Helpers\Formatters\PaginationFormatter;
use App\Http\Requests\AddTaskToProjectRequest;
use App\Http\Requests\GetProjectTasksRequest;
use App\Http\Requests\UpdateProjectTaskRequest;
use App\Models\Enums\DeletionType;
use App\Models\Enums\Status;
use App\Models\Project;
use App\Models\Task;
use Database\Factories\TaskFactory;
use Illuminate\Support\Str;

class TaskRepository
{
    /**
     * User repository class constructor.
     *
     * @param Task $task
     * @param PaginationFormatter $paginationFormatter
     * @param TaskFactory $taskFactory
     * @param SearchQueryBuilderFactory $searchQueryBuilderFactory
     */
    public function __construct(
        protected Task                        $task,
        protected PaginationFormatter         $paginationFormatter,
        protected TaskFactory                 $taskFactory,
        protected SearchQueryBuilderFactory $searchQueryBuilderFactory
    ) {
    }


    /**
     * @param string $id
     *
     * @return Task|null
     * @throws TaskNotFoundException
     */
    public function findWithTrashed(string $id): ?Task
    {
        /** @var Task $project */
        $project = $this->task
            ->newQueryWithoutScopes()
            ->find($id);

        if (!$project) {
            throw new TaskNotFoundException();
        }

        return $project;
    }

    /**
     * @param string $projectId
     * @param string $taskId
     *
     * @return Task|null
     * @throws TaskNotFoundException
     */
    public function getProjectTask(string $projectId, string $taskId): ?Task
    {
        $task = $this->task
            ->query()
            ->where('id', '=', $taskId)
            ->where('project_id', '=', $projectId)
            ->first();

        if (!$task) {
            throw new TaskNotFoundException();
        }

        return $task;
    }

    /**
     * @param GetProjectTasksRequest $request
     * @param string $projectId
     *
     * @return array
     */
    public function searchProjectTasks(GetProjectTasksRequest $request, string $projectId): array
    {
        $query = $this->task->query();

        $this->searchQueryBuilderFactory->create($query)
            ->withProject($projectId)
            ->withStatus($request)
            ->withSorting($request);

        $paginationResult = $query->paginate(
            (int)$request->input('perPage') ?? 20,
            '*',
            'page',
            $request->input('page') ?? 1
        );

        return [
            $paginationResult->items(),
            $this->paginationFormatter->format($paginationResult)
        ];
    }

    /**
     * @param AddTaskToProjectRequest $request
     * @param string $projectId
     *
     * @return Task
     */
    public function addTaskToProject(AddTaskToProjectRequest $request, string $projectId): Task
    {
        $taskTitle = $request->input('title');

        $task = $this->taskFactory->create([
            'project_id' => $projectId,
            'assignee_id' => $request->input('assignee'),
            'title' => $taskTitle,
            'description' => $request->input('description'),
            'difficulty' => $request->input('difficulty'),
            'priority' => $request->input('priority'),
            'status' => Status::OPEN->value,
        ]);

        $task->setAttribute('slug', Str::slug($task->getAttribute('id') . '-' . $taskTitle));
        $task->save();

        return $task;
    }

    /**
     * @param UpdateProjectTaskRequest $request
     * @param Task $task
     *
     * @return Task
     */
    public function updateProjectTask(UpdateProjectTaskRequest $request, Task $task): Task
    {
        if ($title = $request->input('title')) {
            $task->setAttribute('title', $title);
        }

        if ($description = $request->input('description')) {
            $task->setAttribute('description', $description);
        }

        if ($assignee = $request->input('assignee')) {
            $task->setAttribute('assignee', $assignee);
        }

        if ($difficulty = $request->input('difficulty')) {
            $task->setAttribute('difficulty', $difficulty);
        }

        if ($priority = $request->input('priority')) {
            $task->setAttribute('priority', $priority);
        }

        $task->save();

        return $task;
    }

    /**
     * @param Task $task
     *
     * @return Task
     */
    public function closeTask(Task $task): Task
    {
        $task->setAttribute('status', Status::CLOSED->value);
        $task->save();

        return $task;
    }

    /**
     * @param Task $task
     *
     * @return Task
     */
    public function blockTask(Task $task): Task
    {
        $task->setAttribute('status', Status::BLOCKED->value);
        $task->save();

        return $task;
    }

    /**
     * @param Task $task
     *
     * @return Task
     */
    public function openTask(Task $task): Task
    {
        $task->setAttribute('status', Status::OPEN->value);
        $task->save();

        return $task;
    }

    /**
     * @param string $task
     * @param string $type
     *
     * @return void
     * @throws TaskNotFoundException
     */
    public function deleteProjectTask(string $task, string $type): void
    {
        $task = $this->findWithTrashed($task);

        $type === DeletionType::SOFT->value
            ? $task->delete()
            : $task->forceDelete();
    }
}