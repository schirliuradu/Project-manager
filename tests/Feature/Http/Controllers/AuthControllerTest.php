<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Controllers\AuthController
 */
class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithAuthTrait;

    /**
     * @test
     * @covers ::login
     */
    public function should_validate_missing_login_parameters_and_return_validation_messages(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertJsonValidationErrors(['email', 'password']);

        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * @test
     * @covers ::login
     */
    public function should_validate_email_and_return_validation_message(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'hooray',
            'password' => 'loremipsum'
        ]);

        $response->assertJsonValidationErrorFor('email');

        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * @test
     * @covers ::login
     */
    public function should_validate_password_and_return_validation_message(): void
    {
        $response = $this->postJson('/api/login', ['email' => 'test@test.com', 'password' => 'lorem']);

        $response->assertJsonValidationErrorFor('password');
        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * @test
     * @covers ::login
     */
    public function should_throw_user_not_found_exception_if_no_user_with_given_email_in_db(): void
    {
        $this->refreshTestDatabase();

        $fakeUserEmail = 'test@test.com';
        $response = $this->postJson('/api/login', ['email' => $fakeUserEmail, 'password' => 'loremipsum']);

        $response->assertNotFound();
        $this->assertEquals("User not found for email: {$fakeUserEmail}", $response->json()['message']);
    }

    /**
     * @test
     * @covers ::login
     */
    public function should_return_correct_data_if_user_is_registered(): void
    {
        $this->refreshTestDatabase();

        $fakeUser = [
            'email' => 'test@test.com',
            'password' => 'loremipsum'
        ];

        User::factory()->create([
            ...$fakeUser,
            'password' => Hash::make('loremipsum')
        ]);

        $response = $this->postJson('/api/login', $fakeUser);
        $jsonResponse = $response->json();

        $response->assertOk();
        $this->assertEquals('test@test.com', $jsonResponse['user']['email']);
        $this->assertArrayHasKey('token', $jsonResponse);
        $this->assertArrayHasKey('refresh', $jsonResponse);
        $this->assertIsString('token', $jsonResponse['token']);
        $this->assertIsString('refresh', $jsonResponse['refresh']);
    }

    /**
     * @test
     * @covers ::login
     */
    public function should_return_bad_request_status_if_wrong_password_was_given_for_given_user_email(): void
    {
        $this->refreshTestDatabase();

        $fakeUser = [
            'email' => 'test@test.com',
            'password' => 'loremipsum'
        ];

        User::factory()->create([
            ...$fakeUser,
            'password' => Hash::make('loremipsum')
        ]);

        $response = $this->postJson('/api/login', [
            ...$fakeUser,
            'password' => 'loremipsumdifferent'
        ]);

        $response->assertBadRequest();
    }

    /**
     * @test
     * @covers ::register
     */
    public function should_validate_missing_register_parameters_and_return_validation_messages(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertJsonValidationErrors(['email', 'password', 'first_name', 'last_name']);

        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * @test
     * @covers ::register
     */
    public function should_register_new_user_and_return_login_data_for_newly_added_user(): void
    {
        $this->refreshDatabase();

        $response = $this->postJson('/api/register', [
            'email' => 'test@test.com',
            'password' => 'password',
            'first_name' => 'test',
            'last_name' => 'test'
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'user',
            'token',
            'refresh'
        ]);
    }

    /**
     * @test
     * @covers ::refresh
     */
    public function should_validate_missing_refresh_parameters_and_return_validation_message(): void
    {
        $response = $this->postJson('/api/refresh', []);

        $response->assertJsonValidationErrorFor('token');

        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * @test
     * @covers ::refresh
     */
    public function should_refresh_token_and_return_it_as_response(): void
    {
        $response = $this->postJson('/api/refresh', [
            'token' => $this->generateRefreshToken()
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['token']);
        $this->assertIsString($response->json('token'));
    }
}
