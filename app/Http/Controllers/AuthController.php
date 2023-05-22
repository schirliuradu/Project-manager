<?php

namespace App\Http\Controllers;

use App\Exceptions\ExpiredJwtRefreshTokenException;
use App\Exceptions\UnauthorizedUserException;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\WrongPasswordForGivenUserEmailException;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="API Endpoints for Authentication"
 * )
 */
class AuthController extends Controller
{
    /**
     * AuthController class constructor.
     *
     * @param AuthService $authService
     */
    public function __construct(private readonly AuthService $authService)
    {
    }

    /**
     * @OA\Schema(
     *     schema="Login",
     *     @OA\Property(property="id", type="string", example="0056844c-afa2-406b-9989-d49c7e79bc3a"),
     *     @OA\Property(property="first_name", type="string", example="John"),
     *     @OA\Property(property="last_name", type="string", example="Doe"),
     * ),
     * @OA\Schema(
     *     schema="LoginResponse",
     *     @OA\Property(property="user", ref="#/components/schemas/User"),
     *     @OA\Property(property="token", type="string"),
     *     @OA\Property(property="refresh", type="string"),
     * ),
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
     *         @OA\JsonContent(ref="#/components/schemas/LoginResponse"),
     *     ),
     *     @OA\Response(response="400", description="Bad request."),
     *     @OA\Response(response="401", description="Unauthorized."),
     *     @OA\Response(response="422", description="Unprocessable Content.")
     * )
     *
     * @param LoginRequest $request
     *
     * @return JsonResponse
     * @throws UserNotFoundException|WrongPasswordForGivenUserEmailException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return response()->json($this->authService->login($request));
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     operationId="register",
     *     tags={"Auth"},
     *     summary="Register new user.",
     *     description="Endpoint which registers new user and returns jwt credentials.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Register Request",
     *         @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/LoginResponse"),
     *     ),
     *     @OA\Response(response="422", description="Unprocessable Content.")
     * )
     *
     * @param RegisterRequest $request
     *
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        return response()->json($this->authService->register($request));
    }

    /**
     * @OA\Schema(
     *     schema="RefreshResponse",
     *     @OA\Property(property="token", type="string")
     * ),
     *
     * @OA\Post(
     *     path="/api/refresh",
     *     operationId="refresh",
     *     tags={"Auth"},
     *     summary="Refresh token.",
     *     description="Endpoint which refreshes token and returns new jwt access token.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Refresh Request",
     *         @OA\JsonContent(ref="#/components/schemas/RefreshRequest")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/RefreshResponse"),
     *     ),
     *     @OA\Response(response="401", description="Unauthorized."),
     *     @OA\Response(response="422", description="Unprocessable Content.")
     * )
     *
     * @param RefreshTokenRequest $request
     *
     * @return JsonResponse
     * @throws ExpiredJwtRefreshTokenException
     * @throws UnauthorizedUserException
     */
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        return response()->json($this->authService->refreshToken($request));
    }
}
