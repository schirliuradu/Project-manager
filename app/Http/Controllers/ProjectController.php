<?php

namespace App\Http\Controllers;

use App\Exceptions\ProjectNotFoundException;
use App\Http\Requests\AddProjectRequest;
use App\Http\Requests\GetProjectRequest;
use App\Http\Requests\GetProjectsRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Repositories\ProjectRepository;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    /**
     * @param ProjectRepository $repo
     * @param ProjectService $service
     */
    public function __construct(protected ProjectRepository $repo, protected ProjectService $service)
    {
    }


    /**
     * @param GetProjectsRequest $request
     *
     * @return JsonResponse
     */
    public function getProjects(GetProjectsRequest $request): JsonResponse
    {
        return response()->json($this->service->getProjects($request));
    }

    /**
     * @param GetProjectRequest $request
     * @param string $project
     *
     * @return JsonResponse
     */
    public function getProject(GetProjectRequest $request, string $project): JsonResponse
    {
        return response()->json($this->service->getProject($project));
    }

    /**
     * @param AddProjectRequest $request
     *
     * @return JsonResponse
     */
    public function addProject(AddProjectRequest $request): JsonResponse
    {
        return response()->json($this->service->addProject($request));
    }

    /**
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
}
