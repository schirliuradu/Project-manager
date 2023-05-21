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
     * @OA\Schema(
     *     schema="Login",
     *     @OA\Property(property="id", type="string", example="0056844c-afa2-406b-9989-d49c7e79bc3a"),
     *     @OA\Property(property="first_name", type="string", example="John"),
     *     @OA\Property(property="last_name", type="string", example="Doe"),
     * )
     *
     * @OA\Post(
     *     path="/api/login",
     *     operationId="login",
     *     tags={"Auth"},
     *     summary="Login and get jwt token..",
     *     description="Endpoint which logs user in and returns jwt tokens.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Login Request",
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Project"
     *             )
     *         )
     *     ),
     *     @OA\Response(response="422", description="Unprocessable Content.")
     * )
     *
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
            'user' => $user->toArray(),
            'token' => $access,
            'refresh' => $refresh
        ]);
    }
}
