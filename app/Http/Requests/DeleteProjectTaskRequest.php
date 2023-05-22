<?php

namespace App\Http\Requests;

use App\Models\Enums\DeletionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class DeleteProjectTaskRequest extends ApiRequest
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
            'task' => $this->route('task'),
            'type' => $this->route('type')
        ]);
    }
}
