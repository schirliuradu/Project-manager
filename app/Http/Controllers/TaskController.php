<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddTaskToProjectRequest;
use App\Http\Requests\GetProjectTasksRequest;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    /**
     * @param TaskService $service
     */
    public function __construct(protected TaskService $service)
    {
    }

    /**
     * @param GetProjectTasksRequest $request
     * @param string $project
     *
     * @return JsonResponse
     */
    public function getProjectTasks(GetProjectTasksRequest $request, string $project): JsonResponse
    {
        return response()->json($this->service->getProjectTasks($request, $project));
    }

    /**
     * @param AddTaskToProjectRequest $request
     * @param string $project
     *
     * @return JsonResponse
     */
    public function addTaskToProject(AddTaskToProjectRequest $request, string $project): JsonResponse
    {
        return response()->json($this->service->addTaskToProject($request, $project));
    }
}
