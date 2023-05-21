<?php

namespace App\Http\Requests;

use App\Models\Enums\StatusActions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * @OA\Parameter(
 *     parameter="taskStatusUpdateAction",
 *     name="action",
 *     description="Project task status update action",
 *     required=true,
 *     in="path",
 *     @OA\Schema(
 *         type="string",
 *         enum={"open", "block", "close"}
 *     )
 * )
 */
class UpdateProjectTaskStatusRequest extends ApiRequest
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
            'task' => 'required|uuid',
            'action' => Rule::in(StatusActions::allValues())
        ];
    }

    /**
     * @return array
     */
    public function validationData(): array
    {
        return array_merge($this->request->all(), [
            'project' => $this->route('project'),
            'task' => $this->route('task'),
            'action' => $this->route('action'),
        ]);
    }
}
