<?php

namespace App\Http\Requests;

use App\Models\Enums\StatusActions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * @OA\Parameter(
 *     parameter="action",
 *     name="action",
 *     description="Project status update action",
 *     required=true,
 *     in="path",
 *     @OA\Schema(
 *         type="string",
 *         enum={"open", "close"}
 *     )
 * )
 */
class UpdateProjectStatusRequest extends ApiRequest
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
            'action' => Rule::in(StatusActions::basicValues())
        ];
    }

    /**
     * @return array
     */
    public function validationData(): array
    {
        return array_merge($this->request->all(), [
            'project' => $this->route('project'),
            'action' => $this->route('action'),
        ]);
    }
}
