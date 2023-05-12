<?php

namespace Tests\Feature\Http\Middleware;

use App\Exceptions\RequestWithoutBearerException;
use App\Exceptions\UnauthorizedUserException;
use App\Http\Middleware\JwtApiMiddleware;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Middleware\JwtApiMiddleware
 */
class JwtApiMiddlewareTest extends TestCase
{
    /**
     * @test
     * @covers ::handle
     */
    public function should_throw_custom_exception_if_no_bearer_was_found_on_request_object(): void
    {
        // empty request without bearer
        $request = Request::create('/api/projects');

        /**
         * Get middleware instance from container to have auto wiring
         * @var JwtApiMiddleware $middleware
         */
        $middleware = $this->app->get(JwtApiMiddleware::class);

        $this->expectException(RequestWithoutBearerException::class);

        $middleware->handle($request, function () {});
    }

    /**
     * @test
     * @covers ::handle
     */
    public function should_throw_custom_exception_if_there_are_issues_with_token(): void
    {
        $request = Request::create('/api/projects');

        // bind wrong format bearer token on headers
        $request->headers->set('Authorization', 'Bearer loremipsumdolorsitamet');

        /**
         * Get middleware instance from container to have auto wiring
         * @var JwtApiMiddleware $middleware
         */
        $middleware = $this->app->get(JwtApiMiddleware::class);

        $this->expectException(UnauthorizedUserException::class);

        $middleware->handle($request, function () {});
    }
}
