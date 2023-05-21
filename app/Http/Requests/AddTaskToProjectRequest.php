<?php

namespace App\Http\Requests;

use App\Models\Enums\Difficulty;
use App\Models\Enums\Priority;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="AddTaskToProjectRequest",
 *     type="object",
 *     @OA\Property(property="title", type="string", maxLength=255),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="assignee", type="string", example="0056844c-afa2-406b-9989-d49c7e79bc3a"),
 *     @OA\Property(property="difficulty", ref="#/components/schemas/DifficultyEnum"),
 *     @OA\Property(property="priority", ref="#/components/schemas/PriorityEnum")
 * )
 */
class AddTaskToProjectRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'project' => 'required|uuid',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assignee' => 'required|uuid',
            'difficulty' => ['required', Rule::in(Difficulty::values())],
            'priority' => ['required', Rule::in(Priority::values())],
        ];
    }

    /**
     * @return array
     */
    public function validationData(): array
    {
        return array_merge($this->request->all(), [
            'project' => $this->route('project'),
        ]);
    }
}
