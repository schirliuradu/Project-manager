<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Controllers\UserController
 */
class UserControllerTest extends TestCase
{
    use WithAuthTrait, RefreshDatabase;

    /**
     * @test
     * @covers ::updateUser
     */
    public function update_user_should_return_unauthorized_if_no_bearer_was_passed(): void
    {
        $fakeUserId = Str::uuid();

        $response = $this->patch("/api/users/{$fakeUserId}");

        // Assert that the request is unauthorized (401 status code)
        $response->assertUnauthorized();
    }

    /**
     * @test
     * @covers ::updateUser
     */
    public function update_user_should_throw_invalid_user_exception_if_trying_to_update_profile_different_by_bearer_user(): void
    {
        $this->refreshDatabase();

        $otherUser = User::factory()->create(['email' => 'johndoe@test.com']);

        // we auth for `test@test.com` user here but trying to change john doe
        try {
            $response = $this->authAndPatch("/api/users/{$otherUser->id}", [
                'first_name' => 'fake first',
                'last_name' => 'fake last'
            ]);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            dd($e->getMessage());
        }

        $response->assertForbidden();
    }

    /**
     * @test
     * @covers ::updateUser
     */
    public function update_user_should_update_user_and_return_updated_formatted_user_main_data(): void
    {
        $this->refreshDatabase();

        $authenticatedUser = User::factory()->create(['email' => 'test@test.com']);

        // we auth for `test@test.com` and trying to update his own data
        $response = $this->authAndPatch("/api/users/{$authenticatedUser->id}", [
            'first_name' => 'fake first',
            'last_name' => 'fake last'
        ]);

        $response->assertOk();
        $user = User::where('email', '=', 'test@test.com')->first();
        $this->assertEquals('fake first', $user->first_name);
        $this->assertEquals('fake last', $user->last_name);
    }
}
