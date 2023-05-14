<?php

namespace Database\Factories;

use App\Models\Enums\Status;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
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
        ];
    }
}
