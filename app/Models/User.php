<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @OA\Schema(
 *     schema="Assignee",
 *     @OA\Property(property="id", type="string", example="0056844c-afa2-406b-9989-d49c7e79bc3a"),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 * )
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

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
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assignee');
    }

    /**
     * @OA\Schema(
     *     schema="User",
     *     @OA\Property(property="email", type="string", example="johndoe@test.com"),
     *     @OA\Property(property="name", type="string", example="John Doe"),
     * ),
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'name' => $this->first_name . ' ' . $this->last_name,
        ];
    }
}
