<?php

use App\Models\Enums\Difficulty;
use App\Models\Enums\Priority;
use App\Models\Enums\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug');
            $table->string('title');
            $table->text('description');
            $table->uuid('assignee_id');
            $table->uuid('project_id');
            $table->enum('status', Status::values())->default(Status::OPEN->value);
            $table->enum('priority', Priority::values())->default(Priority::MEDIUM->value);
            $table->enum('difficulty', Difficulty::values())->default(Difficulty::FIVE->value);
            $table->timestamps();

            $table->foreign('assignee_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
