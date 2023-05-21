<?php

namespace Tests\Feature\Http\Middleware;

use App\Exceptions\RequestWithoutBearerException;
use App\Exceptions\UnauthorizedUserException;
use App\Http\Middleware\JwtApiMiddleware;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    /**
     * @test
     * @covers ::handle
     */
    public function should_stick_with_next_action_if_token_was_sent_and_was_correctly_parsed(): void
    {
        $request = Request::create('/api/projects');

        /**
         * Get jwt service class instance from container
         * @var JwtService $jwtService
         */
        $jwtService = $this->app->get(JwtService::class);

        try {
            // generate new fake token
            [$fakeBearer, ] = $jwtService->generateTokens('fake_user_id');

        } catch (\Throwable $e) {
            dd($e->getMessage());
        }

        // bind wrong format bearer token on headers
        $request->headers->set('Authorization', "Bearer {$fakeBearer}");

        /**
         * Get middleware instance from container to have auto wiring
         * @var JwtApiMiddleware $middleware
         */
        $middleware = $this->app->get(JwtApiMiddleware::class);

        $response = $middleware->handle($request, function (Request $request) {
            return new Response('Ok');
        });

        $this->assertEquals('Ok', $response->getContent());
    }
}
