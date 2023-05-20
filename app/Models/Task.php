<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Task",
 *     @OA\Property(property="id", type="string", example="0056844c-afa2-406b-9989-d49c7e79bc3b"),
 *     @OA\Property(property="slug", type="string", example="0056844c-afa2-406b-9989-d49c7e79bc3b-lorem-ipsum"),
 *     @OA\Property(property="title", type="string", example="Lorem ipsum"),
 *     @OA\Property(property="description", type="string", example="Lorem ipsum description"),
 *
 *     @OA\Property(property="status", ref="#/components/schemas/StatusEnum"),
 *     @OA\Property(property="priority", ref="#/components/schemas/PriorityEnum"),
 *     @OA\Property(property="difficulty", ref="#/components/schemas/DifficultyEnum"),
 *     @OA\Property(property="assignee", ref="#/components/schemas/Assignee")
 * )
 */
class Task extends Model
{
    use HasFactory;
    use HasUuids;

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
        'assignee_id',
        'project_id',
    ];

    /**
     * Get project assignee.
     *
     * @return BelongsTo
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project that the task belongs to.
     *
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        /** @var User $assignee */
        $assignee = $this->getAttribute('assignee');

        return [
            ...parent::toArray(),
            'assignee' => [
                'id' => $assignee->getAttribute('id'),
                'first_name' => $assignee->getAttribute('first_name'),
                'last_name' => $assignee->getAttribute('last_name'),
            ]
        ];
    }
}
