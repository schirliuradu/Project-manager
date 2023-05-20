<?php

namespace App\Http\Controllers;

use App\Exceptions\ProjectNotFoundException;
use App\Exceptions\TaskNotFoundException;
use App\Http\Requests\AddTaskToProjectRequest;
use App\Http\Requests\GetProjectTaskRequest;
use App\Http\Requests\GetProjectTasksRequest;
use App\Http\Requests\UpdateProjectTaskRequest;
use App\Http\Requests\UpdateProjectTaskStatusRequest;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    /**
     * @param TaskService $service
     */
    public function __construct(protected TaskService $service)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/projects/{project}/tasks",
     *     operationId="getProjectTasks",
     *     tags={"Tasks"},
     *     summary="Get project task list",
     *     description="Endpoint which retrieves list of paginated tasks related to given project.",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(ref="#/components/parameters/project"),
     *     @OA\Parameter(ref="#/components/parameters/pageParameter"),
     *     @OA\Parameter(ref="#/components/parameters/perPageParameter"),
     *     @OA\Parameter(ref="#/components/parameters/sortByParameter"),
     *     @OA\Parameter(ref="#/components/parameters/withClosedParameter"),
     *     @OA\Parameter(ref="#/components/parameters/onlyClosedParameter"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Task")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Resource not found."),
     *     @OA\Response(response="422", description="Unprocessable Content.")
     * )
     *
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

    /**
     * @param UpdateProjectTaskRequest $request
     * @param string $project
     * @param string $task
     *
     * @return JsonResponse
     * @throws ProjectNotFoundException
     * @throws TaskNotFoundException
     */
    public function updateProjectTask(UpdateProjectTaskRequest $request, string $project, string $task): JsonResponse
    {
        return response()->json($this->service->updateProjectTask($request, $project, $task));
    }

    /**
     * @param UpdateProjectTaskStatusRequest $request
     * @param string $project
     * @param string $task
     * @param string $action
     *
     * @return Response
     */
    public function updateProjectTaskStatus(
        UpdateProjectTaskStatusRequest $request,
        string $project,
        string $task,
        string $action
    ): Response
    {
        $this->service->updateProjectTaskStatus($project, $task, $action);

        return response()->noContent();
    }
}
