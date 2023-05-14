<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddProjectRequest;
use App\Http\Requests\GetProjectRequest;
use App\Http\Requests\GetProjectsRequest;
use App\Repositories\ProjectRepository;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    /**
     * @param ProjectRepository $repo
     */
    public function __construct(protected ProjectRepository $repo)
    {
    }


    /**
     * @param GetProjectsRequest $request
     *
     * @return JsonResponse
     */
    public function getProjects(GetProjectsRequest $request): JsonResponse
    {
        [$data, $meta] = $this->repo->searchProjects($request);

        return response()->json([
            'data' => $data,
            'meta' => $meta
        ]);
    }

    /**
     * @param GetProjectRequest $request
     * @param string $project
     *
     * @return JsonResponse
     */
    public function getProject(GetProjectRequest $request, string $project): JsonResponse
    {
        return response()->json([
            'data' => $this->repo->find($project)
        ]);
    }

    /**
     * @param AddProjectRequest $request
     *
     * @return JsonResponse
     */
    public function addProject(AddProjectRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->repo->addProject($request)
        ]);
    }
}
