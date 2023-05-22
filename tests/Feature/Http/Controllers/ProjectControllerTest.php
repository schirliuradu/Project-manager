<?php

namespace Tests\Feature\Http\Controllers;

use App\Exceptions\ProjectNotFoundException;
use App\Models\Enums\Status;
use App\Models\Project;
use App\Repositories\ProjectRepository;
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

        $response->assertJsonValidationErrors(['page', 'perPage', 'sortBy']);

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

        $response->assertOk();

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

        $response->assertJsonValidationErrorFor('project');

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

        $response->assertNotFound();
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

        $response->assertOk();

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

        $response->assertJsonValidationErrors(['title', 'description']);

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
        $response->assertOk();

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
        // Make a PATCH request to the '/api/projects' endpoint without authentication
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

        $responseWrongParams->assertJsonValidationErrors(['title', 'description']);
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
        $response->assertOk();

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

    /**
     * @test
     * @covers ::updateProjectStatus
     */
    public function update_project_status_should_return_unauthorized_if_no_bearer_was_passed(): void
    {
        // Make a PATCH request to the '/api/projects' endpoint without authentication
        $response = $this->patch('/api/projects/fake_uuid/open', []);

        // Assert that the request is unauthorized (401 status code)
        $response->assertUnauthorized();
    }

    /**
     * @test
     * @covers ::updateProjectStatus
     */
    public function update_project_status_should_return_not_found_status_if_project_does_not_exist(): void
    {
        $fakeProjectId = Str::uuid();

        $response = $this->authAndPatch("/api/projects/{$fakeProjectId}/open");

        // Assert that the request is unauthorized (401 status code)
        $response->assertNotFound();
    }

    /**
     * @test
     * @covers ::updateProjectStatus
     */
    public function update_project_status_should_validate_action_value(): void
    {
        $fakeProjectId = Str::uuid();
        $response = $this->authAndPatch("/api/projects/{$fakeProjectId}/fake");
        $response->assertStatus(422);
    }

    /**
     * @test
     * @covers ::updateProjectStatus
     */
    public function update_project_status_should_return_no_content_response_if_status_was_updated(): void
    {
        $this->refreshDatabase();

        $project = Project::factory()->create(['status' => Status::OPEN->value]);

        $response = $this->authAndPatch("/api/projects/{$project->id}/close");

        $this->assertEquals(Status::CLOSED->value, Project::find($project->id)->status);
        $response->assertNoContent();
    }

    /**
     * @test
     * @covers ::updateProjectStatus
     */
    public function update_project_status_should_bad_request_if_trying_to_reopen_a_closed_project(): void
    {
        $this->refreshDatabase();

        $project = Project::factory()->create(['status' => Status::CLOSED->value]);
        $project->status = Status::CLOSED->value;

        $response = $this->authAndPatch("/api/projects/{$project->id}/open");

        $response->assertBadRequest();
    }

    /**
     * @test
     * @covers ::deleteProject
     */
    public function delete_project_should_return_unauthorized_if_no_bearer_was_passed(): void
    {
        $fakeProjectId = Str::uuid();

        $response = $this->delete("/api/projects/{$fakeProjectId}/soft");

        // Assert that the request is unauthorized (401 status code)
        $response->assertUnauthorized();
    }

    /**
     * @test
     * @covers ::deleteProject
     */
    public function delete_project_should_return_input_validation_error_if_input_is_not_ok(): void
    {
        $response = $this->authAndDelete("/api/projects/wrong_uuid/wrong_type");

        $response->assertJsonValidationErrors(['project', 'type']);

        // Assert the response status code is 422
        $response->assertStatus(422);
    }

    /**
     * @test
     * @covers ::deleteProject
     */
    public function delete_project_should_return_not_found_if_given_project_does_not_exist(): void
    {
        $fakeProjectId = Str::uuid();

        $response = $this->authAndDelete("/api/projects/{$fakeProjectId}/soft");

        $response->assertNotFound();
    }

    /**
     * @test
     * @covers ::deleteProject
     */
    public function delete_project_should_soft_delete_a_project(): void
    {
        $this->refreshDatabase();

        $project = Project::factory()->create();
        $response = $this->authAndDelete("/api/projects/{$project->id}/soft");
        $response->assertNoContent();

        /** @var ProjectRepository $projectRepository */
        $projectRepository = $this->app->get(ProjectRepository::class);
        $softlyDeletedProject = $projectRepository->findWithTrashed($project->id);
        $this->assertNotNull($softlyDeletedProject->getAttribute('deleted_at'));
    }

    /**
     * @test
     * @covers ::deleteProject
     */
    public function delete_project_should_hard_delete_a_project(): void
    {
        $this->refreshDatabase();

        $project = Project::factory()->create();
        $response = $this->authAndDelete("/api/projects/{$project->id}/hard");
        $response->assertNoContent();

        /** @var ProjectRepository $projectRepository */
        $projectRepository = $this->app->get(ProjectRepository::class);

        $this->expectException(ProjectNotFoundException::class);
        $projectRepository->findWithTrashed($project->id);
    }
}
