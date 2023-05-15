<?php

namespace App\Http\Controllers;

use App\Exceptions\ProjectNotFoundException;
use App\Exceptions\TaskNotFoundException;
use App\Http\Requests\AddTaskToProjectRequest;
use App\Http\Requests\GetProjectTaskRequest;
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
     * @param GetProjectTaskRequest $request
     * @param string $project
     * @param string $task
     *
     * @return JsonResponse
     * @throws TaskNotFoundException
     */
    public function getProjectTask(GetProjectTaskRequest $request, string $project, string $task): JsonResponse
    {
        return response()->json($this->service->getProjectTask($request, $project, $task));
    }

    /**
     * @param AddTaskToProjectRequest $request
     * @param string $project
     *
     * @return JsonResponse
     * @throws ProjectNotFoundException
     */
    public function addTaskToProject(AddTaskToProjectRequest $request, string $project): JsonResponse
    {
        return response()->json($this->service->addTaskToProject($request, $project));
    }
}
