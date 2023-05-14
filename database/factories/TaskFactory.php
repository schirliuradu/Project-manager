<?php

namespace Database\Factories;

use App\Models\Enums\Complexity;
use App\Models\Enums\Priority;
use App\Models\Enums\Status;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create();

        return [
            'id' => Str::uuid(),
            'title' => $faker->sentence(3),
            'slug' => Str::slug($faker->sentence(3)),
            'description' => $faker->paragraph(4),
            'status' => $faker->randomElement(Status::values()),
            'priority' => $faker->randomElement(Priority::values()),
            'complexity' => $faker->randomElement(Complexity::values()),
            'assignee_id' => User::all()->random()->id,
            'project_id' => Project::all()->random()->id,
        ];
    }
}
