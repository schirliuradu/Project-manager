<?php

namespace App\Http\Middleware;

use App\Exceptions\ExpiredJwtTokenException;
use App\Exceptions\RequestWithoutBearerException;
use App\Exceptions\UnauthorizedUserException;
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
     * @throws UnauthorizedUserException
     * @throws RequestWithoutBearerException
     * @throws ExpiredJwtTokenException
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$bearer = $request->bearerToken()) {
            throw new RequestWithoutBearerException();
        }

        try {
            $this->jwtService->parseAndValidateToken($bearer);
            return $next($request);

        } catch (Exception $e) {
            throw ($e instanceof ExpiredJwtTokenException)
                ? $e
                : new UnauthorizedUserException();
        }
    }
}