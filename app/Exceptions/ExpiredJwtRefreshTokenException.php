<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ExpiredJwtRefreshTokenException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => "Your refresh token has expired. Please login again."
        ], 401);
    }
}
