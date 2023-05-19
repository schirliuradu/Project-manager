<?php

namespace App\Http\Controllers;

use App\Exceptions\ProjectNotFoundException;
use App\Http\Requests\AddProjectRequest;
use App\Http\Requests\GetProjectRequest;
use App\Http\Requests\GetProjectsRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Requests\UpdateProjectStatusRequest;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    /**
     * @param ProjectService $service
     */
    public function __construct(protected ProjectService $service)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/projects",
     *     operationId="getProjects",
     *     tags={"Projects"},
     *     summary="Get project list",
     *     description="Endpoint which retrieves list of paginated projects.",
     *     security={{"bearerAuth": {}}},
     *
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
     *                 @OA\Items(ref="#/components/schemas/Project")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Resource not found."),
     *     @OA\Response(response="422", description="Unprocessable Content.")
     * )
     *
     * @param GetProjectsRequest $request
     *
     * @return JsonResponse
     */
    public function getProjects(GetProjectsRequest $request): JsonResponse
    {
        return response()->json($this->service->getProjects($request));
    }

    /**
     * @OA\Get(
     *     path="/api/projects/{project}",
     *     operationId="getProject",
     *     tags={"Projects"},
     *     summary="Get single project by id",
     *     description="Endpoint which retrieves single project by id..",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(ref="#/components/parameters/project"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Project"
     *             )
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Resource not found."),
     *     @OA\Response(response="422", description="Unprocessable Content.")
     * )
     *
     * @param GetProjectRequest $request
     * @param string $project
     *
     * @return JsonResponse
     * @throws ProjectNotFoundException
     */
    public function getProject(GetProjectRequest $request, string $project): JsonResponse
    {
        return response()->json($this->service->getProject($project));
    }

    /**
     * @OA\Post(
     *     path="/api/projects",
     *     operationId="addProject",
     *     tags={"Projects"},
     *     summary="Add new project.",
     *     description="Endpoint which adds new project.",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Add Project Request",
     *         @OA\JsonContent(ref="#/components/schemas/AddProjectRequest")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Project"
     *             )
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="422", description="Unprocessable Content.")
     * )
     *
     * @param AddProjectRequest $request
     *
     * @return JsonResponse
     */
    public function addProject(AddProjectRequest $request): JsonResponse
    {
        return response()->json($this->service->addProject($request));
    }

    /**
     * @OA\Patch(
     *     path="/api/projects/{project}",
     *     operationId="updateProject",
     *     tags={"Projects"},
     *     summary="Update existing project.",
     *     description="Endpoint which updates already existing project.",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(ref="#/components/parameters/project"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Update Project Request",
     *         @OA\JsonContent(ref="#/components/schemas/UpdateProjectRequest")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Project"
     *             )
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="422", description="Unprocessable Content.")
     * )
     *
     * @param UpdateProjectRequest $request
     * @param string $project
     *
     * @return JsonResponse
     * @throws ProjectNotFoundException
     */
    public function updateProject(UpdateProjectRequest $request, string $project): JsonResponse
    {
        return response()->json($this->service->updateProject($request, $project));
    }

    /**
     * @param UpdateProjectStatusRequest $request
     * @param string $project
     * @param string $status
     *
     * @return Response
     * @throws ProjectNotFoundException
     */
    public function updateProjectStatus(UpdateProjectStatusRequest $request, string $project, string $status): Response
    {
        $this->service->updateProjectStatus($project, $status);

        return response()->noContent();
    }
}
