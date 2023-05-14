<?php

namespace App\Repositories;

use App\Helpers\Formatters\PaginationFormatter;
use App\Http\Requests\GetProjectTasksRequest;
use App\Models\Task;
use Database\Factories\ProjectFactory;

class TaskRepository
{
    use SearchTrait;

    /**
     * User repository class constructor.
     *
     * @param Task $task
     * @param PaginationFormatter $paginationFormatter
     * @param ProjectFactory $projectFactory
     */
    public function __construct(
        protected Task                $task,
        protected PaginationFormatter $paginationFormatter,
        protected ProjectFactory      $projectFactory
    ) {
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
}