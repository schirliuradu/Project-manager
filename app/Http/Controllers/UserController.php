<?php

namespace App\Http\Controllers;

use App\Exceptions\ExpiredJwtTokenException;
use App\Exceptions\InvalidUserException;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="API Endpoints for Users"
 * )
 */
class UserController extends Controller
{
    /**
     * @param UserService $service
     */
    public function __construct(protected UserService $service)
    {
    }

    /**
     * @OA\Patch(
     *     path="/api/users/{user}",
     *     operationId="updateUser",
     *     tags={"Users"},
     *     summary="Update existing user data.",
     *     description="Endpoint which updates already existing user data.",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(ref="#/components/parameters/user"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Update User Request",
     *         @OA\JsonContent(ref="#/components/schemas/UpdateUserRequest")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/User"
     *             )
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized."),
     *     @OA\Response(response="403", description="Forbidden."),
     *     @OA\Response(response="404", description="Resource not found."),
     *     @OA\Response(response="422", description="Unprocessable Content.")
     * )
     *
     * @param UpdateUserRequest $request
     * @param string $id
     *
     * @return JsonResponse
     * @throws ExpiredJwtTokenException
     * @throws InvalidUserException
     */
    public function updateUser(UpdateUserRequest $request, string $id): JsonResponse
    {
        return response()->json($this->service->updateUser($request, $id));
    }
}
