<?php

namespace App\Repositories;

use App\Exceptions\UserNotFoundException;
use App\Models\User;

class UserRepository
{
    /**
     * User repository class constructor.
     *
     * @param User $user
     */
    public function __construct(protected User $user)
    {
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->user
            ->query()
            ->where('email', $email)
            ->first();
    }

    /**
     * @param string $id
     *
     * @return User|null
     */
    public function find(string $id): ?User
    {
        return $this->user
            ->query()
            ->where('id', '=', $id)
            ->first();
    }
}
