<?php

namespace App\Repositories;

use App\Helpers\Formatters\PaginationFormatter;
use App\Http\Requests\AddTaskToProjectRequest;
use App\Http\Requests\GetProjectTasksRequest;
use App\Models\Enums\Status;
use App\Models\Task;
use Database\Factories\ProjectFactory;
use Database\Factories\TaskFactory;
use Illuminate\Support\Str;

class TaskRepository
{
    use SearchTrait;

    /**
     * User repository class constructor.
     *
     * @param Task $task
     * @param PaginationFormatter $paginationFormatter
     * @param TaskFactory $taskFactory
     */
    public function __construct(
        protected Task                $task,
        protected PaginationFormatter $paginationFormatter,
        protected TaskFactory      $taskFactory
    ) {
    }


    /**
     * @param string $projectId
     * @param string $taskId
     *
     * @return Task|null
     */
    public function getProjectTask(string $projectId, string $taskId): ?Task
    {
        return $this->task
            ->query()
            ->where('id', '=', $taskId)
            ->where('project_id', '=', $projectId)
            ->first();
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

        $query->where('project_id', '=', $projectId);

        // handle STATUS filters
        $this->bindStatusFilterLogic($request, $query);

        // handle sorting
        $this->bindSortingFilterLogic($request, $query);

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

        $task->setAttribute('slug', $task->getAttribute('id') . '-' . Str::slug($taskTitle));
        $task->save();

        return $task;
    }
}