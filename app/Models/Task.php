<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
                'first_nae' => $assignee->getAttribute('first_name'),
                'last_name' => $assignee->getAttribute('last_name'),
            ]
        ];
    }
}
