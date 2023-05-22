<?php

namespace App\Http\Requests;

use App\Models\Enums\DeletionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * @OA\Parameter(
 *     parameter="type",
 *     name="type",
 *     description="Deletion type.",
 *     required=true,
 *     in="path",
 *     @OA\Schema(ref="#/components/schemas/DeletionTypeEnum")
 * )
 */
class DeleteProjectRequest extends ApiRequest
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
            'type' => ['required', Rule::in(DeletionType::values())]
        ];
    }

    /**
     * @return array
     */
    public function validationData(): array
    {
        return array_merge($this->request->all(), [
            'project' => $this->route('project'),
            'type' => $this->route('type')
        ]);
    }
}
