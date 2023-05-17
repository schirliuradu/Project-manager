<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Controllers\ProjectController
 */
class ProjectControllerTest extends TestCase
{
    use RefreshDatabase, WithAuthTrait;

    /**
     * @test
     * @covers ::getProjects
     */
    public function get_projects_should_return_unauthorized_if_no_bearer_was_passed(): void
    {
        // Make a GET request to the '/api/projects' endpoint without authentication
        $response = $this->get('/api/projects');

        // Assert that the request is unauthorized (401 status code)
        $response->assertUnauthorized();
    }

    /**
     * @test
     * @covers ::getProjects
     */
    public function get_projects_should_return_input_validation_error_if_input_is_not_ok(): void
    {
        // Make a GET request to the '/api/projects' endpoint with bearer
        $response = $this->authAndGet('/api/projects');
        $arrayResponse = $response->json();

        $this->assertArrayHasKey('errors', $arrayResponse);
        $this->assertEquals(['page', 'perPage', 'sortBy'], array_keys($arrayResponse['errors']));

        // Assert the response status code is 422
        $response->assertStatus(422);
    }

    /**
     * @test
     * @covers ::getProjects
     */
    public function get_projects_should_return_correctly_formatted_response_data(): void
    {
        $this->refreshTestDatabase();
        $this->refreshDatabase();
        Project::factory(5)->create();

        // Make a GET request to the '/api/projects' endpoint with bearer
        $response = $this->authAndGet('/api/projects?page=1&perPage=5&sortBy=alpha_asc&withClosed=1');

        $response->assertStatus(200);

        // Assert the response structure and content
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'slug',
                    'title',
                    'description',
                    'status',
                    'tasks_count',
                    'completed_tasks_count',
                ]
            ],
            'meta' => []
        ]);
    }
}