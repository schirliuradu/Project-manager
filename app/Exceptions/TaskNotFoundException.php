<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class TaskNotFoundException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => "Task not found!"
        ], 404);
    }
}
