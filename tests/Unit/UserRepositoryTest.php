<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * @coversDefaultClass \App\Repositories\UserRepository
 */
class UserRepositoryTest extends UnitTestCase
{
    private UserRepository $repo;
    private User $modelMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modelMock = \Mockery::mock(User::class);
        $this->repo = new UserRepository($this->modelMock);
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
}
