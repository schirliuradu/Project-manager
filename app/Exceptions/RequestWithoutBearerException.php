<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class RequestWithoutBearerException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => "Forgot to send bearer token in your request. Try again!"
        ], 401);
    }
}
