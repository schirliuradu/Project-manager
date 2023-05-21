<?php

namespace App\Services;

use App\Exceptions\ExpiredJwtTokenException;
use App\Exceptions\InvalidUserException;
use App\Http\Requests\UpdateUserRequest;
use App\Repositories\UserRepository;

class UserService
{
    /**
     * @param UserRepository $userRepository
     * @param JwtService $jwtService
     */
    public function __construct(
        protected UserRepository $userRepository,
        protected JwtService $jwtService
    ) {
    }

    /**
     * @param UpdateUserRequest $request
     * @param string $id
     *
     * @return array
     * @throws ExpiredJwtTokenException
     * @throws InvalidUserException
     */
    public function updateUser(UpdateUserRequest $request, string $id): array
    {
        $userId = $this->jwtService->getUserIdFromToken($request->bearerToken());

        // try to update someone's else profile
        if ($userId !== $id) {
            throw new InvalidUserException();
        }

        $user = $this->userRepository->find($id);

        return [
            'data' => $this->userRepository
                ->updateUser($user, $request)
                ->toArray()
        ];
    }
}