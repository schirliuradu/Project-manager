<?php

namespace App\Http\Controllers;

use App\Exceptions\UserNotFoundException;
use App\Http\Requests\LoginRequest;
use App\Repositories\UserRepository;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * AuthController class constructor.
     *
     * @param JwtService $jwtService
     * @param UserRepository $userRepository
     */
    public function __construct(
        private readonly JwtService     $jwtService,
        private readonly UserRepository $userRepository
    ) {
    }

    /**
     * @param LoginRequest $request
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->userRepository->getUserByEmail($request->input('email'));

        if (!$user) {
            throw new UserNotFoundException();
        }

        [$access, $refresh] = $this->jwtService->generateTokens($user->getAttribute('id'));

        return response()->json([
            'user' => [
                'email' => $user->getAttribute('email'),
                'name' => $user->getAttribute('name'),
            ],
            'token' => $access,
            'refresh' => $refresh
        ]);
    }
}
