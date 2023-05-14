<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ProjectNotFoundException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => "Project not found for given id!"
        ], 404);
    }
}
