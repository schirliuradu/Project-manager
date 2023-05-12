<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Symfony\Component\HttpFoundation\Response;

class JwtApiMiddleware
{
    /**
     * @param JwtService $jwtService
     */
    public function __construct(protected JwtService $jwtService)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->jwtService->parseAndValidateToken($request->bearerToken());
            return $next($request);

        } catch (Exception|RequiredConstraintsViolated $e) {
            // @todo better erro handling with custom exceptions
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}