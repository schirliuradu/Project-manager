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
     * @throws ProjectNotFoundException
     */
    public function getProjectTasks(GetProjectTasksRequest $request, string $project): JsonResponse
    {
        return response()->json($this->service->getProjectTasks($request, $project));
    }

    /**
     * @OA\Get(
     *     path="/api/projects/{project}/tasks/{task}",
     *     operationId="getProjectTask",
     *     tags={"Tasks"},
     *     summary="Get single project task by id",
     *     description="Endpoint which retrieves single project task by id.",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(ref="#/components/parameters/project"),
     *     @OA\Parameter(ref="#/components/parameters/task"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Task"
     *             )
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Resource not found."),
     *     @OA\Response(response="422", description="Unprocessable Content.")
     * )
     *
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
     * @OA\Post(
     *     path="/api/projects/{project}/tasks",
     *     operationId="addTaskToProject",
     *     tags={"Tasks"},
     *     summary="Add new task to existing project.",
     *     description="Endpoint which adds new task to an existing project.",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(ref="#/components/parameters/project"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Add Task To Project Request",
     *         @OA\JsonContent(ref="#/components/schemas/AddTaskToProjectRequest")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Task"
     *             )
     *         )
     *     ),
     *     @OA\Response(response="400", description="Bad Request."),
     *     @OA\Response(response="401", description="Unauthorized."),
     *     @OA\Response(response="404", description="Resource not found."),
     *     @OA\Response(response="422", description="Unprocessable Content.")
     * )
     *
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
     * @OA\Patch(
     *     path="/api/projects/{project}/tasks/{task}",
     *     operationId="updateProjectTask",
     *     tags={"Tasks"},
     *     summary="Update existing project task.",
     *     description="Endpoint which updates already existing project task.",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(ref="#/components/parameters/project"),
     *     @OA\Parameter(ref="#/components/parameters/task"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Update Project Task Request",
     *         @OA\JsonContent(ref="#/components/schemas/UpdateProjectTaskRequest")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Task"
     *             )
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Resource not found."),
     *     @OA\Response(response="422", description="Unprocessable Content.")
     * )
     *
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
     * @throws ProjectNotFoundException
     * @throws TaskNotFoundException
     */
    public function updateProjectTaskStatus(
        UpdateProjectTaskStatusRequest $request,
        string $project,
        string $task,
        string $action
    ): Response {
        $this->service->updateProjectTaskStatus($project, $task, $action);

        return response()->noContent();
    }
}
