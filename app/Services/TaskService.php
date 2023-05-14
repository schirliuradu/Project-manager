<?php

namespace App\Services;

use App\Http\Requests\GetProjectTasksRequest;
use App\Repositories\TaskRepository;

class TaskService
{
    /**
     * @param TaskRepository $repo
     */
    public function __construct(protected TaskRepository $repo)
    {
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
}