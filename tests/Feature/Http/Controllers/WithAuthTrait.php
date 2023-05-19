<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Testing\TestResponse;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait WithAuthTrait
{
    /**
     * Helper method.
     *
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function authAndGet(string $endpoint): TestResponse
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->bearer(),
        ])->get($endpoint);
    }

    /**
     * @param string $endpoint
     * @param array $data
     *
     * @return TestResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function authAndPost(string $endpoint, array $data): TestResponse
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->bearer(),
        ])->post($endpoint, $data);
    }

    /**
     * @param string $endpoint
     * @param array $data
     *
     * @return TestResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function authAndPatch(string $endpoint, array $data = []): TestResponse
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->bearer(),
        ])->patch($endpoint, $data);
    }
}