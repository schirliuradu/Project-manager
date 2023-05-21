<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class GetProjectTaskRequest extends ApiRequest
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
            'task' => 'required|uuid'
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
        ]);
    }
}
