<?php

namespace App\Repositories;

use App\Exceptions\UserNotFoundException;
use App\Models\User;
use Database\Factories\UserFactory;

class UserRepository
{
    /**
     * User repository class constructor.
     *
     * @param User $user
     * @param UserFactory $factory
     */
    public function __construct(
        protected User $user,
        protected UserFactory $factory
    ) {
    }

    /**
     * @param string $email
     *
     * @return User|null
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

    /**
     * @param array $user
     *
     * @return User
     */
    public function addUser(array $user): User
    {
        return $this->factory->create($user);
    }
}
