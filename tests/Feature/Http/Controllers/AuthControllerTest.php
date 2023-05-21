<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Controllers\AuthController
 */
class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

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

        $fakeUser = ['email' => 'test@test.com', 'password' => 'loremipsum'];
        User::factory()->create($fakeUser);

        $response = $this->postJson('/api/login', $fakeUser);
        $jsonResponse = $response->json();

        $response->assertOk();
        $this->assertEquals('test@test.com', $jsonResponse['user']['email']);
        $this->assertArrayHasKey('token', $jsonResponse);
        $this->assertArrayHasKey('refresh', $jsonResponse);
        $this->assertIsString('token', $jsonResponse['token']);
        $this->assertIsString('refresh', $jsonResponse['refresh']);
    }
}
