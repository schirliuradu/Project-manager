<?php

namespace App\Repositories;

use App\Helpers\Formatters\PaginationFormatter;
use App\Models\Enums\SortingValues;
use App\Models\Enums\Status;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProjectRepository
{
    /**
     * User repository class constructor.
     *
     * @param Project $project
     * @param PaginationFormatter $paginationFormatter
     */
    public function __construct(
        protected Project             $project,
        protected PaginationFormatter $paginationFormatter
    )
    {
    }

    /**
     * Get a project by its ID.
     *
     * @param string $id
     *
     * @return Project|null
     */
    public function find(string $id): ?Project
    {
        return Project::find($id);
    }

    /**
     * Method which helps to search through all projects with input filters.
     *
     * @param Request $request
     *
     * @return array
     */
    public function searchProjects(Request $request): array
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

        $query->withCount([
            'tasks',
            'tasks as completed_tasks_count' => function ($query) {
                $query->where('status', '=', Status::CLOSED->value);
            }
        ]);

        $paginationResult = $query->paginate(
            (int)$request->input('perPage') ?? 20,
            '*',
            'page',
            $request->input('page') ?? 1
        );

        return [
            'data' => $paginationResult->items(),
            'meta' => $this->paginationFormatter->format($paginationResult)
        ];
    }
}
