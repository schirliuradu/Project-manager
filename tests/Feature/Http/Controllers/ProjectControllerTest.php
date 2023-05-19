<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

    /**
     * @test
     * @covers ::getProject
     */
    public function get_project_should_return_unauthorized_if_no_bearer_was_passed(): void
    {
        // Make a GET request to the '/api/projects' endpoint without authentication
        $response = $this->get('/api/projects/502ae527-d673-427e-b059-fb2c2de2243f');

        // Assert that the request is unauthorized (401 status code)
        $response->assertUnauthorized();
    }

    /**
     * @test
     * @covers ::getProject
     */
    public function get_project_should_return_input_validation_error_if_input_is_not_ok(): void
    {
        // Make a GET request to the '/api/projects' endpoint with bearer
        $response = $this->authAndGet('/api/projects/fake_uuid');
        $arrayResponse = $response->json();

        $this->assertArrayHasKey('errors', $arrayResponse);
        $this->assertEquals(['project'], array_keys($arrayResponse['errors']));

        // Assert the response status code is 422
        $response->assertStatus(422);
    }

    /**
     * @test
     * @covers ::getProject
     */
    public function get_project_should_return_not_found_status_if_no_project_was_found_for_given_id(): void
    {
        // valid uuid but not existing project in db
        $fakeProjectId = Str::uuid();

        // Make a GET request to the '/api/projects/id' endpoint with bearer
        $response = $this->authAndGet("/api/projects/{$fakeProjectId}");

        $response->assertStatus(404);
    }

    /**
     * @test
     * @covers ::getProject
     */
    public function get_project_should_return_correctly_formatted_response_data(): void
    {
        $this->refreshDatabase();
        $project = Project::factory()->create();

        // Make a GET request to the '/api/projects/id' endpoint with bearer
        $response = $this->authAndGet("/api/projects/{$project->id}");

        $response->assertStatus(200);

        $this->assertEquals([
            'data' => [
                'id' => $project->id,
                'slug' => $project->slug,
                'title' => $project->title,
                'description' => $project->description,
                'status' => $project->status,
                'tasks_count' => $project->tasks_count,
                'completed_tasks_count' => $project->completed_tasks_count,
            ]
        ], $response->json());
    }

    /**
     * @test
     * @covers ::addProject
     */
    public function add_project_should_return_unauthorized_if_no_bearer_was_passed(): void
    {
        // Make a POST request to the '/api/projects' endpoint without authentication
        $response = $this->post('/api/projects', []);

        // Assert that the request is unauthorized (401 status code)
        $response->assertUnauthorized();
    }

    /**
     * @test
     * @covers ::addProject
     */
    public function add_project_should_return_input_validation_error_if_input_is_not_ok(): void
    {
        // Make a GET request to the '/api/projects' endpoint with bearer
        $response = $this->authAndPost('/api/projects', []);
        $arrayResponse = $response->json();

        $this->assertArrayHasKey('errors', $arrayResponse);
        $this->assertEquals(['title', 'description'], array_keys($arrayResponse['errors']));

        // Assert the response status code is 422
        $response->assertStatus(422);
    }

    /**
     * @test
     * @covers ::addProject
     */
    public function add_project_should_return_correctly_formatted_response_data(): void
    {
        $fakePostData = [
            'title' => 'lorem ipsum',
            'description' => 'dolor sit amet'
        ];

        $response = $this->authAndPost('/api/projects', $fakePostData);
        $response->assertStatus(200);

        // Assert the response structure and content
        $response->assertJsonStructure([
            'data' => [
                'id',
                'slug',
                'title',
                'description',
                'status',
                'tasks_count',
                'completed_tasks_count',
            ]
        ]);
    }

    /**
     * @test
     * @covers ::updateProject
     */
    public function update_project_should_return_unauthorized_if_no_bearer_was_passed(): void
    {
        // Make a POST request to the '/api/projects' endpoint without authentication
        $response = $this->patch('/api/projects/fake_uuid', []);

        // Assert that the request is unauthorized (401 status code)
        $response->assertUnauthorized();
    }

    /**
     * @test
     * @covers ::updateProject
     */
    public function update_project_should_return_input_validation_error_if_project_id_is_invalid(): void
    {
        $this->refreshDatabase();

        // request with wrong uuid format
        $response = $this->authAndPatch('/api/projects/fake_uuid', []);
        $response->assertStatus(422);
    }

    /**
     * @test
     * @covers ::updateProject
     */
    public function update_project_should_return_input_validation_error_if_both_title_and_description_are_missing(): void
    {
        $this->refreshDatabase();

        // request with correct project id but without stuff to be changed
        $project = Project::factory()->create();
        $responseWrongParams = $this->authAndPatch("/api/projects/{$project->id}", []);
        $responseWrongParams->assertStatus(422);

        $arrayResponse = $responseWrongParams->json();
        $this->assertArrayHasKey('errors', $arrayResponse);
        $this->assertEquals(['title', 'description'], array_keys($arrayResponse['errors']));
    }

    /**
     * @test
     * @covers ::updateProject
     */
    public function update_project_should_return_correctly_formatted_updated_project_data(): void
    {
        $fakePostData = [
            'title' => 'lorem ipsum updated',
            'description' => 'dolor sit amet updated'
        ];

        $project = Project::factory()->create();

        $response = $this->authAndPatch("/api/projects/{$project->id}", $fakePostData);
        $response->assertStatus(200);

        // Assert the response structure and content
        $response->assertJsonStructure([
            'data' => [
                'id',
                'slug',
                'title',
                'description',
                'status',
                'tasks_count',
                'completed_tasks_count',
            ]
        ]);

        $jsonResponse = $response->json();
        $this->assertEquals($fakePostData['title'], $jsonResponse['data']['title']);
        $this->assertEquals($fakePostData['description'], $jsonResponse['data']['description']);
    }
}
