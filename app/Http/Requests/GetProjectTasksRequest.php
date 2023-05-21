<?php

namespace App\Http\Requests;

use App\Models\Enums\SortingValues;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * @OA\Parameter(
 *     parameter="task",
 *     name="task",
 *     description="Task id",
 *     required=true,
 *     in="path",
 *     @OA\Schema(
 *         type="string"
 *     )
 * )
 */
class GetProjectTasksRequest extends ApiRequest
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
            'page' => 'required|integer',
            'perPage' => 'required|integer',
            'sortBy' => [
                'required',
                Rule::in(SortingValues::values())
            ],
            'withClosed' => 'boolean',
            'onlyClosed' => 'boolean',
        ];
    }

    /**
     * @return array
     */
    public function validationData(): array
    {
        return array_merge($this->input(), [
            'project' => $this->route('project'),
        ]);
    }
}
