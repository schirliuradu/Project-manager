<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // fake user to auth with
        \App\Models\User::factory()->create([
            'email' => 'test@test.com',
            'password' => 'password',
            'first_name' => 'Test',
            'last_name' => 'Test',
        ]);

        \App\Models\User::factory(10)->create();
    }
}
