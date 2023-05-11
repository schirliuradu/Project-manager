<?php

namespace Tests\Unit\Services;

use App\Services\JwtService;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Services\JwtService
 */
class JwtServiceTest extends TestCase
{

    /**
     * @test
     * @covers ::generateTokens
     */
    public function should_return_both_access_and_refresh_tokens_as_strings(): void
    {
        $userId = 1111;
        $fakeAccessToken = 'hooray-access-token';
        $fakeRefreshToken = 'hooray-refresh-token';

        $service = \Mockery::mock(JwtService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('generateAccessToken')
            ->once()
            ->with($userId)
            ->andReturn($fakeAccessToken);

        $service->shouldReceive('generateRefreshToken')
            ->once()
            ->with($userId)
            ->andReturn($fakeRefreshToken);

        $tokens = $service->generateTokens($userId);

        $this->assertCount(2, $tokens);
        $this->assertEquals([$fakeAccessToken, $fakeRefreshToken], $tokens);
    }
}