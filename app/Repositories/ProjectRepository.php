<?php

namespace App\Repositories;

use App\Exceptions\ProjectNotFoundException;
use App\Helpers\Formatters\PaginationFormatter;
use App\Http\Requests\AddProjectRequest;
use App\Http\Requests\GetProjectsRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Enums\SortingValues;
use App\Models\Enums\Status;
use App\Models\Project;
use Database\Factories\ProjectFactory;
use Illuminate\Support\Str;

class ProjectRepository
{
    /**
     * User repository class constructor.
     *
     * @param Project $project
     * @param PaginationFormatter $paginationFormatter
     * @param ProjectFactory $projectFactory
     */
    public function __construct(
        protected Project             $project,
        protected PaginationFormatter $paginationFormatter,
        protected ProjectFactory $projectFactory
    )
    {
    }

    /**
     * Get a project by its ID.
     *
     * @param string $id
     *
     * @return array
     */
    public function find(string $id): array
    {
        return $this->project
            ->query()
            ->where('id', '=', $id)
            ->first()
            ->toArray();
    }

    /**
     * @param UpdateProjectRequest $request
     * @param string $id
     *
     * @return array
     * @throws ProjectNotFoundException
     */
    public function updateProject(UpdateProjectRequest $request, string $id): array
    {
        $project = $this->project
            ->query()
            ->where('id', '=', $id)
            ->first();

        if (!$project) {
            throw new ProjectNotFoundException();
        }

        $project->title = $request->input('title');
        $project->description = $request->input('description');

        $project->save();

        return $project->toArray();
    }

    /**
     * Method which helps to search through all projects with input filters.
     *
     * @param GetProjectsRequest $request
     *
     * @return array
     */
    public function searchProjects(GetProjectsRequest $request): array
    {
        $query = $this->project->query();

        // handle STATUS filters
        $query->when($request->input('withClosed'), function ($query) {
            $query->whereIn('status', Status::values());
        }, function ($query) use ($request) {
            $query->where('status', '=', $request->input('onlyClosed')
                ? Status::CLOSED->value
                : Status::OPEN->value
            );
        });

        // handle sorting
        if ($sortBy = $request->input('sortBy')) {
            switch ($sortBy) {
                case SortingValues::ALPHADESC->value:
                    $query->orderBy('title', 'desc');
                    break;

                case SortingValues::ALPHAASC->value:
                    $query->orderBy('title');
                    break;

                case SortingValues::UPDATE->value:
                    $query->orderBy('updated_at');
                    break;

                case SortingValues::CREATE->value:
                default:
                    $query->orderBy('created_at');
                    break;
            }
        }

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

    /**
     * @param AddProjectRequest $request
     *
     * @return array
     */
    public function addProject(AddProjectRequest $request): array
    {
        $projectTitle = $request->input('title');

        $model = $this->projectFactory->create([
            'title' => $projectTitle,
            'description' => $request->input('description'),
            'status' => Status::OPEN->value,
        ]);

        $model->slug = $model->id . '-' . Str::slug($projectTitle);
        $model->save();

        return $model->toArray();
    }
}
