<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @OA\Parameter(
 *     parameter="project",
 *     name="project",
 *     description="Project id",
 *     required=true,
 *     in="path",
 *     @OA\Schema(
 *         type="string"
 *     )
 * )
 */
class GetProjectRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'project' => 'required|uuid'
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
