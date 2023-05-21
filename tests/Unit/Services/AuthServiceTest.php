<?php

namespace Tests\Unit\Services;

use App\Exceptions\ExpiredJwtRefreshTokenException;
use App\Exceptions\UnauthorizedUserException;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\WrongPasswordForGivenUserEmailException;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\JwtService;
use Illuminate\Support\Facades\Hash;
use Tests\Unit\UnitTestCase;

/**
 * @coversDefaultClass \App\Services\AuthService
 */
class AuthServiceTest extends UnitTestCase
{
    protected AuthService $service;

    protected JwtService $jwtServiceMock;
    protected UserRepository $userRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtServiceMock = \Mockery::mock(JwtService::class);
        $this->userRepositoryMock = \Mockery::mock(UserRepository::class);

        $this->service = new AuthService($this->jwtServiceMock, $this->userRepositoryMock);
    }

    /**
     * @test
     * @covers ::login
     */
    public function login_should_throw_user_not_found_custom_exception(): void
    {
        $fakeRequestMock = \Mockery::mock(LoginRequest::class);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('email')
            ->andReturn($fakeEmail = 'test@test.com');

        $this->userRepositoryMock->shouldReceive('getUserByEmail')
            ->once()
            ->with($fakeEmail)
            ->andReturnNull();

        $this->expectException(UserNotFoundException::class);

        $this->service->login($fakeRequestMock);
    }

    /**
     * @test
     * @covers ::login
     */
    public function login_should_throw_wrong_password_custom_exception(): void
    {
        $fakeRequestMock = \Mockery::mock(LoginRequest::class);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('email')
            ->andReturn($fakeEmail = 'test@test.com');
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('password')
            ->andReturn('password');

        $fakeUserMock = \Mockery::mock(User::class);
        $fakeUserMock->shouldReceive('getAttribute')
            ->once()
            ->with('password')
            ->andReturn('passworddifferent');

        $this->userRepositoryMock->shouldReceive('getUserByEmail')
            ->once()
            ->with($fakeEmail)
            ->andReturn($fakeUserMock);

        Hash::shouldReceive('check')
            ->once()
            ->andReturnFalse();

        $this->expectException(WrongPasswordForGivenUserEmailException::class);

        $this->service->login($fakeRequestMock);
    }

    /**
     * @test
     * @covers ::login
     */
    public function login_should_generate_jwt_tokens_and_return_them_as_final_formatted_array(): void
    {
        $fakeRequestMock = \Mockery::mock(LoginRequest::class);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('password')
            ->andReturn('password');
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('email')
            ->andReturn($fakeEmail = 'test@test.com');

        $fakeUserMock = \Mockery::mock(User::class);
        $fakeUserMock->shouldReceive('getAttribute')
            ->once()
            ->with('password')
            ->andReturn('passworddifferent');
        $fakeUserMock->shouldReceive('toArray')
            ->once()
            ->andReturn($fakeUserArray = ['email' => $fakeEmail, 'name' => 'test']);
        $fakeUserMock->shouldReceive('getAttribute')
            ->once()
            ->with('id')
            ->andReturn($fakeUserId = 'fake_user_uuid');

        $this->userRepositoryMock->shouldReceive('getUserByEmail')
            ->once()
            ->with($fakeEmail)
            ->andReturn($fakeUserMock);

        Hash::shouldReceive('check')
            ->once()
            ->andReturnTrue();

        $this->jwtServiceMock->shouldReceive('generateTokens')
            ->once()
            ->with($fakeUserId)
            ->andReturn([$fakeAccessToken = 'fake_access_token', $fakeRefreshToken = 'fake_refresh_token']);

        $this->assertEquals([
            'user' => $fakeUserArray,
            'token' => $fakeAccessToken,
            'refresh' => $fakeRefreshToken
        ], $this->service->login($fakeRequestMock));
    }

    /**
     * @test
     * @covers ::register
     */
    public function register_should_generate_new_user_and_jwt_tokens_and_return_them_as_final_formatted_array(): void
    {
        $fakeRequestMock = \Mockery::mock(RegisterRequest::class);
        $fakeRequestMock->shouldReceive('only')
            ->once()
            ->with(['email', 'password', 'first_name', 'last_name'])
            ->andReturn([
                'email' => 'test@test.com',
                'password' => 'password',
                'first_name' => 'test',
                'last_name' => 'test'
            ]);

        $fakeUserMock = \Mockery::mock(User::class);
        $fakeUserMock->shouldReceive('getAttribute')
            ->once()
            ->with('id')
            ->andReturn($fakeUserId = 'fake_user_uuid');
        $fakeUserMock->shouldReceive('toArray')
            ->once()
            ->andReturn($fakeUserArray = ['email' => 'test@test.com', 'name' => 'test']);

        $this->userRepositoryMock->shouldReceive('addUser')
            ->once()
            ->andReturn($fakeUserMock);

        $this->jwtServiceMock->shouldReceive('generateTokens')
            ->once()
            ->with($fakeUserId)
            ->andReturn([$fakeAccessToken = 'fake_access_token', $fakeRefreshToken = 'fake_refresh_token']);

        $this->assertEquals([
            'user' => $fakeUserArray,
            'token' => $fakeAccessToken,
            'refresh' => $fakeRefreshToken
        ], $this->service->register($fakeRequestMock));
    }

    /**
     * @test
     * @covers ::refreshToken
     */
    public function refresh_token_should_bubble_up_custom_exception_if_given_refresh_token_has_expired(): void
    {
        $fakeRequestMock = \Mockery::mock(RefreshTokenRequest::class);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('token')
            ->andReturn('fake_refresh_token');

        $this->jwtServiceMock->shouldReceive('refreshAccessToken')
            ->once()
            ->andThrow(ExpiredJwtRefreshTokenException::class);

        $this->expectException(ExpiredJwtRefreshTokenException::class);
        $this->service->refreshToken($fakeRequestMock);
    }

    /**
     * @test
     * @covers ::refreshToken
     */
    public function refresh_token_should_bubble_up_custom_exception_if_something_went_wrong(): void
    {
        $fakeRequestMock = \Mockery::mock(RefreshTokenRequest::class);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('token')
            ->andReturn('fake_refresh_token');

        $this->jwtServiceMock->shouldReceive('refreshAccessToken')
            ->once()
            ->andThrow(UnauthorizedUserException::class);

        $this->expectException(UnauthorizedUserException::class);
        $this->service->refreshToken($fakeRequestMock);
    }

    /**
     * @test
     * @covers ::refreshToken
     */
    public function refresh_token_should_generate_new_user_and_jwt_tokens_and_return_them_as_final_formatted_array(): void
    {
        $fakeRequestMock = \Mockery::mock(RefreshTokenRequest::class);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with('token')
            ->andReturn('fake_refresh_token');

        $this->jwtServiceMock->shouldReceive('refreshAccessToken')
            ->once()
            ->andReturn($fakeAccessToken = 'fake_new_access_token');

        $this->assertEquals([
            'token' => $fakeAccessToken
        ], $this->service->refreshToken($fakeRequestMock));
    }
}
