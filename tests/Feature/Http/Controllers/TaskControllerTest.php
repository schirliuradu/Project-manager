<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Enums\Difficulty;
use App\Models\Enums\Priority;
use App\Models\Enums\Status;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Controllers\TaskController
 */
class TaskControllerTest extends TestCase
{
    use RefreshDatabase, WithAuthTrait;

    /**
     * @test
     * @covers ::getProjectTasks
     */
    public function get_project_tasks_should_return_unauthorized_if_no_bearer_was_passed(): void
    {
        $fakeUuid = Str::uuid();

        $response = $this->get("/api/projects/{$fakeUuid}/tasks");

        // Assert that the request is unauthorized (401 status code)
        $response->assertUnauthorized();
    }

    /**
     * @test
     * @covers ::getProjectTasks
     */
    public function get_project_tasks_should_return_input_validation_error_if_input_is_not_ok(): void
    {
        $fakeUuid = Str::uuid();

        $response = $this->authAndGet("/api/projects/{$fakeUuid}/tasks");
        $arrayResponse = $response->json();

        $this->assertArrayHasKey('errors', $arrayResponse);
        $this->assertEquals(['page', 'perPage', 'sortBy'], array_keys($arrayResponse['errors']));

        $response->assertStatus(422);
    }

    /**
     * @test
     * @covers ::getProjectTasks
     */
    public function get_project_tasks_should_return_not_found_status_if_project_was_not_found_for_given_id(): void
    {
        $this->refreshDatabase();
        $fakeUuid = Str::uuid();

        $response = $this->authAndGet("/api/projects/{$fakeUuid}/tasks?page=1&perPage=5&sortBy=alpha_desc");

        $response->assertNotFound();
    }

    /**
     * @test
     * @covers ::getProjectTasks
     */
    public function get_projects_should_return_correctly_formatted_response_data(): void
    {
        $this->refreshDatabase();

        User::factory()->create();
        $project = Project::factory()->create();
        Task::factory(5)->create();

        $response = $this->authAndGet("/api/projects/{$project->id}/tasks?page=1&perPage=5&sortBy=alpha_asc&with_closed=1");

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
                    'priority',
                    'difficulty',
                    'assignee'
                ]
            ],
            'meta' => []
        ]);
    }

    /**
     * @test
     * @covers ::addTaskToProject
     */
    public function add_task_to_project_should_return_unauthorized_if_no_bearer_was_passed(): void
    {
        $fakeUuid = Str::uuid();

        $response = $this->post("/api/projects/{$fakeUuid}/tasks");

        // Assert that the request is unauthorized (401 status code)
        $response->assertUnauthorized();
    }

    /**
     * @test
     * @covers ::addTaskToProject
     */
    public function add_task_to_project_should_return_input_validation_error_if_input_is_not_ok(): void
    {
        $fakeUuid = Str::uuid();

        $response = $this->authAndPost("/api/projects/{$fakeUuid}/tasks");
        $arrayResponse = $response->json();

        $this->assertArrayHasKey('errors', $arrayResponse);
        $this->assertEquals(
            ['title', 'description', 'assignee', 'difficulty', 'priority'],
            array_keys($arrayResponse['errors'])
        );

        $response->assertStatus(422);
    }

    /**
     * @test
     * @covers ::addTaskToProject
     */
    public function add_task_to_project_should_return_not_found_status_if_project_was_not_found_for_given_id(): void
    {
        $this->refreshDatabase();
        $fakeUuid = Str::uuid();

        $response = $this->authAndPost("/api/projects/{$fakeUuid}/tasks", [
            'title' => 'lorem',
            'description' => 'ipsum',
            'assignee' => $fakeUuid->toString(),
            'difficulty' => Difficulty::FIVE->value,
            'priority' => Priority::HIGH->value
        ]);

        $response->assertNotFound();
    }

    /**
     * @test
     * @covers ::addTaskToProject
     */
    public function add_task_to_project_should_return_correctly_formatted_response_data(): void
    {
        $this->refreshDatabase();

        $user = User::factory()->create();
        $project = Project::factory()->create();

        $response = $this->authAndPost("/api/projects/{$project->id}/tasks", [
            'title' => 'lorem',
            'description' => 'ipsum',
            'assignee' => $user->id->toString(),
            'difficulty' => Difficulty::FIVE->value,
            'priority' => Priority::HIGH->value
        ]);

        $response->assertStatus(200);

        // Assert the response structure and content
        $response->assertJsonStructure([
            'data' => [
                'id',
                'slug',
                'title',
                'description',
                'status',
                'priority',
                'difficulty',
                'assignee'
            ]
        ]);
    }

    /**
     * @test
     * @covers ::getProjectTask
     */
    public function get_project_task_should_return_unauthorized_if_no_bearer_was_passed(): void
    {
        $fakeUuid = Str::uuid();

        $response = $this->get("/api/projects/{$fakeUuid}/tasks/{$fakeUuid}");

        // Assert that the request is unauthorized (401 status code)
        $response->assertUnauthorized();
    }

    /**
     * @test
     * @covers ::getProjectTask
     */
    public function get_project_task_should_return_input_validation_error_if_input_is_not_ok(): void
    {
        $response = $this->authAndGet("/api/projects/wrong_project_id/tasks/wrong_task_id");
        $arrayResponse = $response->json();

        $this->assertArrayHasKey('errors', $arrayResponse);
        $this->assertEquals(['project', 'task'], array_keys($arrayResponse['errors']));

        $response->assertStatus(422);
    }

    /**
     * @test
     * @covers ::getProjectTask
     */
    public function get_project_task_should_return_not_found_status_if_project_was_not_found_for_given_id(): void
    {
        $this->refreshDatabase();
        $fakeUuid = Str::uuid();

        $response = $this->authAndGet("/api/projects/{$fakeUuid}/tasks/{$fakeUuid}");

        $response->assertNotFound();
    }

    /**
     * @test
     * @covers ::getProjectTask
     */
    public function get_project_task_should_return_correctly_formatted_response_data(): void
    {
        $this->refreshDatabase();
        User::factory()->create();
        $project = Project::factory()->create();
        $task = Task::factory()->create();

        $response = $this->authAndGet("/api/projects/{$project->id}/tasks/{$task->id}");

        $response->assertStatus(200);

        // Assert the response structure and content
        $response->assertJsonStructure([
            'data' => [
                'id',
                'slug',
                'title',
                'description',
                'status',
                'priority',
                'difficulty',
                'assignee'
            ]
        ]);
    }
}
