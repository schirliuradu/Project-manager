<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserNotFoundException extends Exception
{

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => "User not found for email: {$request->input('email')}"
        ], 404);
    }
}
