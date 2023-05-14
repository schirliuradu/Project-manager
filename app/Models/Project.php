<?php

namespace App\Models;

use App\Models\Enums\Status;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
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
    ];

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
