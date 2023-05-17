<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Testing\TestResponse;

trait WithAuthTrait
{
    /**
     * Helper method.
     *
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function bearer(): string
    {
        /**
         * Get jwt service class instance from container
         * @var JwtService $jwtService
         */
        $jwtService = $this->app->get(JwtService::class);

        User::factory()->create(['email' => 'test@test.com', 'password' => 'loremipsum']);

        return $jwtService->generateTokens(1111)[0];
    }

    /**
     * @param string $endpoint
     *
     * @return TestResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function authAndGet(string $endpoint): TestResponse
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->bearer(),
        ])->get($endpoint);
    }
}