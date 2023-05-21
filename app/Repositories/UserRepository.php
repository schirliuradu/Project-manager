<?php

namespace App\Repositories;

use App\Exceptions\UserNotFoundException;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Hash;

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
        return $this->factory->create([
            ...$user,
            'password' => Hash::make($user['password'])
        ]);
    }

    /**
     * @param User $user
     * @param UpdateUserRequest $request
     *
     * @return User
     */
    public function updateUser(User $user, UpdateUserRequest $request): User
    {
        if ($firstName = $request->input('first_name')) {
            $user->setAttribute('first_name', $firstName);
        }

        if ($lastName = $request->input('last_name')) {
            $user->setAttribute('last_name', $lastName);
        }

        if ($password = $request->input('password')) {
            $user->setAttribute('password', Hash::make($password));
        }

        $user->save();

        return $user;
    }
}
