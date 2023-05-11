<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    /**
     * User model instance.
     *
     * @var User
     */
    protected User $user;

    /**
     * User repository class constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->user
            ->where('email', $email)
            ->first();
    }
}
