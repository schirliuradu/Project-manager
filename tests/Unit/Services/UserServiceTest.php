<?php

namespace Tests\Unit\Services;

use App\Exceptions\InvalidUserException;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\JwtService;
use App\Services\UserService;
use Tests\Unit\UnitTestCase;

/**
 * @coversDefaultClass \App\Services\UserService
 */
class UserServiceTest extends UnitTestCase
{
    protected UserService $service;

    protected UserRepository $userRepositoryMock;
    protected JwtService $jwtServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepositoryMock = \Mockery::mock(UserRepository::class);
        $this->jwtServiceMock = \Mockery::mock(JwtService::class);

        $this->service = new UserService($this->userRepositoryMock, $this->jwtServiceMock);
    }

    /**
     * @test
     * @covers ::updateUser
     */
    public function update_user_should_throw_invalid_user_exception_if_user_is_trying_to_update_other_users_data(): void
    {
        $fakeRequestMock = \Mockery::mock(UpdateUserRequest::class);
        $fakeRequestMock->shouldReceive('bearerToken')
            ->once()
            ->andReturn($fakeToken = 'fake_token');

        $this->jwtServiceMock->shouldReceive('getUserIdFromToken')
            ->once()
            ->with($fakeToken)
            ->andReturn('fake_uuid');

        $this->expectException(InvalidUserException::class);

        $this->service->updateUser($fakeRequestMock, 'fake_uuid_other_user');
    }

    /**
     * @test
     * @covers ::updateUser
     */
    public function update_user_should_return_user_updated_data_as_wrapped_formatted_array(): void
    {
        $fakeRequestMock = \Mockery::mock(UpdateUserRequest::class);
        $fakeRequestMock->shouldReceive('bearerToken')
            ->once()
            ->andReturn($fakeToken = 'fake_token');

        $this->jwtServiceMock->shouldReceive('getUserIdFromToken')
            ->once()
            ->with($fakeToken)
            ->andReturn($fakeUserId = 'fake_uuid');

        $fakeUserMock = \Mockery::mock(User::class);
        $fakeUserMock->shouldReceive('toArray')
            ->andReturn($fakeUserToArray = ['fake_user_to_array']);

        $this->userRepositoryMock->shouldReceive('find')
            ->once()
            ->with($fakeUserId)
            ->andReturn($fakeUserMock);

        $this->userRepositoryMock->shouldReceive('updateUser')
            ->once()
            ->with($fakeUserMock, $fakeRequestMock)
            ->andReturn($fakeUserMock);

        $this->assertEquals([
            'data' => $fakeUserToArray
        ], $this->service->updateUser($fakeRequestMock, $fakeUserId));
    }
}
