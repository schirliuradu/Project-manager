<?php

namespace App\Http\Controllers;

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
        return response()->json($this->repo->searchProjects($request));
    }
}
