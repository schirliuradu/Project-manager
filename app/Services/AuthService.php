<?php

namespace App\Services;

use App\Exceptions\ExpiredJwtRefreshTokenException;
use App\Exceptions\GenericErrorException;
use App\Exceptions\UnauthorizedUserException;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\WrongPasswordForGivenUserEmailException;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * @param JwtService $jwtService
     * @param UserRepository $userRepository
     */
    public function __construct(
        protected JwtService $jwtService,
        protected UserRepository $userRepository
    ) {
    }

    /**
     * @param LoginRequest $request
     *
     * @return array
     * @throws UserNotFoundException
     * @throws WrongPasswordForGivenUserEmailException
     */
    public function login(LoginRequest $request): array
    {
        $user = $this->userRepository->getUserByEmail($request->input('email'));

        if (!$user) {
            throw new UserNotFoundException();
        }

        if (!Hash::check($request->input('password'), $user->getAttribute('password'))) {
            throw new WrongPasswordForGivenUserEmailException();
        }

        [$access, $refresh] = $this->jwtService->generateTokens($user->getAttribute('id'));

        return [
            'user' => $user->toArray(),
            'token' => $access,
            'refresh' => $refresh
        ];
    }

    /**
     * @param RegisterRequest $request
     *
     * @return array
     */
    public function register(RegisterRequest $request): array
    {
        $user = $this->userRepository->addUser($request->only(['email', 'password', 'first_name', 'last_name']));

        [$access, $refresh] = $this->jwtService->generateTokens($user->getAttribute('id'));

        return [
            'user' => $user->toArray(),
            'token' => $access,
            'refresh' => $refresh
        ];
    }

    /**
     * @param RefreshTokenRequest $request
     *
     * @return array
     * @throws ExpiredJwtRefreshTokenException
     * @throws UnauthorizedUserException
     */
    public function refreshToken(RefreshTokenRequest $request): array
    {
        return ['token' => $this->jwtService->refreshAccessToken($request->input('token'))];
    }
}