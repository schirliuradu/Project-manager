<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ExpiredJwtTokenException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => "Your token has expired. Please refresh it or login again."
        ], 401);
    }
}
