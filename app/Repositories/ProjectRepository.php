<?php

namespace App\Repositories;

use App\Exceptions\ProjectNotFoundException;
use App\Factories\SearchQueryBuilderFactory;
use App\Helpers\Formatters\PaginationFormatter;
use App\Http\Requests\AddProjectRequest;
use App\Http\Requests\GetProjectsRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Enums\DeletionType;
use App\Models\Enums\Status;
use App\Models\Project;
use App\Models\Scopes\NotDeletedScope;
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
     * @param SearchQueryBuilderFactory $searchQueryBuilderFactory
     */
    public function __construct(
        protected Project             $project,
        protected PaginationFormatter $paginationFormatter,
        protected ProjectFactory $projectFactory,
        protected SearchQueryBuilderFactory $searchQueryBuilderFactory
    )
    {
    }

    /**
     * Get a project by its ID.
     *
     * @param string $id
     *
     * @return Project | null
     * @throws ProjectNotFoundException
     */
    public function find(string $id): ?Project
    {
        /** @var Project $project */
        $project = $this->project
            ->query()
            ->where('id', '=', $id)
            ->first();

        if (!$project) {
            throw new ProjectNotFoundException();
        }

        return $project;
    }

    /**
     * @param string $id
     *
     * @return Project|null
     * @throws ProjectNotFoundException
     */
    public function findWithTrashed(string $id): ?Project
    {
        /** @var Project $project */
        $project = $this->project
            ->newQueryWithoutScopes()
            ->find($id);

        if (!$project) {
            throw new ProjectNotFoundException();
        }

        return $project;
    }

    /**
     * @param Project $project
     * @param UpdateProjectRequest $request
     *
     * @return Project
     */
    public function updateProject(Project $project, UpdateProjectRequest $request): Project
    {
        // update title only if new one was passed - it's not required
        if ($title = $request->input('title')) {
            $project->setAttribute('title', $title);
        }

        // same here for the description
        if ($description = $request->input('description')) {
            $project->setAttribute('description', $description);
        }

        $project->save();

        return $project;
    }

    /**
     * @param Project $project
     *
     * @return Project
     */
    public function closeProject(Project $project): Project
    {
        $project->setAttribute('status', Status::CLOSED->value);
        $project->save();

        return $project;
    }

    /**
     * @param Project $project
     *
     * @return Project
     */
    public function openProject(Project $project): Project
    {
        $project->setAttribute('status', Status::OPEN->value);
        $project->save();

        return $project;
    }

    /**
     * @param Project $project
     *
     * @return bool
     */
    public function hasOpenedTasks(Project $project): bool
    {
        return $project->openedTasks()->count() > 0;
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

        $this->searchQueryBuilderFactory->create($query)
            ->withStatus($request)
            ->withSorting($request);

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
     * @return Project
     */
    public function addProject(AddProjectRequest $request): Project
    {
        $projectTitle = $request->input('title');

        $project = $this->projectFactory->create([
            'title' => $projectTitle,
            'description' => $request->input('description'),
            'status' => Status::OPEN->value,
        ]);

        $project->setAttribute('slug',  Str::slug($project->getAttribute('id') . '-' . $projectTitle));
        $project->save();

        return $project;
    }

    /**
     * @param string $id
     * @param string $type
     *
     * @return void
     * @throws ProjectNotFoundException
     */
    public function deleteProject(string $id, string $type): void
    {
        $project = $this->findWithTrashed($id);

        $type === DeletionType::SOFT->value
            ? $project->delete()
            : $project->forceDelete();
    }
}
