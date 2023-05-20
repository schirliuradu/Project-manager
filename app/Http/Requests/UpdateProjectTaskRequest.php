<?php

namespace App\Http\Requests;

use App\Models\Enums\Difficulty;
use App\Models\Enums\Priority;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="UpdateProjectTaskRequest",
 *     type="object",
 *     @OA\Property(property="title", type="string", maxLength=255),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="assignee", type="string", example="0056844c-afa2-406b-9989-d49c7e79bc3a"),
 *     @OA\Property(property="difficulty", ref="#/components/schemas/DifficultyEnum"),
 *     @OA\Property(property="priority", ref="#/components/schemas/PriorityEnum")
 * )
 */
class UpdateProjectTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'project' => 'required|uuid',
            'task' => 'required|uuid',
            'title' => 'string|max:255|required_without_all:description, assignee, difficulty, priority',
            'description' => 'string|required_without_all:title, assignee, difficulty, priority',
            'assignee' => 'uuid|required_without_all:title, description, difficulty, priority',
            'difficulty' => [
                'required_without_all:title, description, assignee, priority',
                Rule::in(Difficulty::values())
            ],
            'priority' => [
                'required_without_all:title, description, assignee, difficulty',
                Rule::in(Priority::values())
            ]
        ];
    }

    /**
     * @return array
     */
    public function validationData(): array
    {
        return array_merge($this->request->all(), [
            'project' => $this->route('project'),
            'task' => $this->route('task')
        ]);
    }

    /**
     * @param Validator $validator
     *
     * @return void
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            new JsonResponse(['errors' => $validator->errors()], 422)
        );
    }
}
