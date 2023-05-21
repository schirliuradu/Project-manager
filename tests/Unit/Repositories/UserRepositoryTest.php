<?php

namespace Tests\Unit\Repositories;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Unit\UnitTestCase;

/**
 * @coversDefaultClass \App\Repositories\UserRepository
 */
class UserRepositoryTest extends UnitTestCase
{
    private UserRepository $repo;

    private User $modelMock;
    private UserFactory $userFactoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modelMock = \Mockery::mock(User::class);
        $this->userFactoryMock = \Mockery::mock(UserFactory::class);

        $this->repo = new UserRepository($this->modelMock, $this->userFactoryMock);
    }

    /**
     * @test
     * @covers ::getUserByEmail
     */
    public function get_user_by_email_returns_null_if_no_user_was_found_for_given_email(): void
    {
        $queryMock = \Mockery::mock(Builder::class);
        $queryMock->shouldReceive('where')
            ->once()
            ->with('email', $email = 'test@test.com')
            ->andReturnSelf();
        $queryMock->shouldReceive('first')
            ->once()
            ->with()
            ->andReturnNull();

        $this->modelMock->shouldReceive('query')
            ->once()
            ->andReturn($queryMock);

        $this->assertNull($this->repo->getUserByEmail($email));
    }

    /**
     * @test
     * @covers ::getUserByEmail
     */
    public function get_user_by_email_returns_user_model_instance_correctly(): void
    {
        $queryMock = \Mockery::mock(Builder::class);
        $queryMock->shouldReceive('where')
            ->once()
            ->with('email', $email = 'test@test.com')
            ->andReturnSelf();
        $queryMock->shouldReceive('first')
            ->once()
            ->with()
            ->andReturn($this->modelMock);

        $this->modelMock->shouldReceive('query')
            ->once()
            ->andReturn($queryMock);

        $this->assertEquals($this->modelMock, $this->repo->getUserByEmail($email));
    }

    /**
     * @test
     * @covers ::find
     */
    public function find_method_returns_null_if_no_user_was_found_for_given_id(): void
    {
        $queryMock = \Mockery::mock(Builder::class);
        $queryMock->shouldReceive('where')
            ->once()
            ->with('id', '=', $id = Str::uuid()->toString())
            ->andReturnSelf();
        $queryMock->shouldReceive('first')
            ->once()
            ->with()
            ->andReturnNull();

        $this->modelMock->shouldReceive('query')
            ->once()
            ->andReturn($queryMock);

        $this->assertNull($this->repo->find($id));
    }

    /**
     * @test
     * @covers ::find
     */
    public function find_method_returns_user_model_instance_correctly(): void
    {
        $queryMock = \Mockery::mock(Builder::class);
        $queryMock->shouldReceive('where')
            ->once()
            ->with('id', '=', $id = Str::uuid()->toString())
            ->andReturnSelf();
        $queryMock->shouldReceive('first')
            ->once()
            ->with()
            ->andReturn($this->modelMock);

        $this->modelMock->shouldReceive('query')
            ->once()
            ->andReturn($queryMock);

        $this->assertEquals($this->modelMock, $this->repo->find($id));
    }


    /**
     * @test
     * @covers ::updateUser
     * @dataProvider updateUserDataProvider
     */
    public function update_user_should_set_request_properties_to_existing_user_correctly(
        string $property,
        string $value
    ): void {
        $fakeRequestMock = \Mockery::mock(UpdateUserRequest::class);
        $fakeRequestMock->shouldReceive('input')
            ->once()
            ->with($property)
            ->andReturn($value);
        $fakeRequestMock->shouldReceive('input')
            ->twice();

        $fakeUserMock = \Mockery::mock(User::class);
        $fakeUserMock->shouldReceive('setAttribute')
            ->once()
            ->with($property, $value)
            ->andReturnSelf();

        // mock hash facade only for password property
        if ($property === 'password') {
            Hash::shouldReceive('make')
                ->once()
                ->andReturn($value);
        }

        $fakeUserMock->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->assertEquals($fakeUserMock, $this->repo->updateUser($fakeUserMock, $fakeRequestMock));
    }

    /**
     * Data provider for update user test cases.
     *
     * @return array
     */
    public static function updateUserDataProvider(): array
    {
        return [
            ['first_name', 'john'],
            ['last_name', 'doe'],
            ['password', 'changedpassword']
        ];
    }
}
