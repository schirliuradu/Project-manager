<?php

namespace App\Models;

use App\Models\Enums\Status;
use App\Models\Scopes\NotDeletedScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Project",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         example="0056844c-afa2-406b-9989-d49c7e79bc3a"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         example="0056844c-afa2-406b-9989-d49c7e79bc3a-lorem-ipsum"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         example="Lorem ipsum"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         example="Dolor sit amet consecteur"
 *     ),
 *     @OA\Property(property="status", ref="#/components/schemas/StatusEnum"),
 *     @OA\Property(
 *         property="tasks_count",
 *         type="string",
 *         example="Total tasks count, both closed and open"
 *     ),
 *     @OA\Property(
 *         property="completed_tasks_count",
 *         type="string",
 *         example="Completed tasks count."
 *     )
 * )
 *
 */
class Project extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var string[]
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new NotDeletedScope());
    }

    /**
     * Get the original query builder for a model instance.
     *
     * @return Builder
     */
    public function newQueryWithoutScopes(): Builder
    {
        return parent::newQueryWithoutScopes()->withoutGlobalScope('notDeleted');
    }

    /**
     * Get the tasks for the project.
     *
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * @return HasMany
     */
    public function completedTasks(): HasMany
    {
        return $this->tasks()->where('status', '=', Status::CLOSED->value);
    }

    /**
     * @return HasMany
     */
    public function openedTasks(): HasMany
    {
        return $this->tasks()->where('status', '=', Status::OPEN->value);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'tasks_count' => $this->tasks()->count(),
            'completed_tasks_count' => $this->completedTasks()->count(),
        ];
    }
}
