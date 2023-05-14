<?php

namespace App\Http\Requests;

use App\Models\Enums\SortingValues;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class GetProjectTasksRequest extends FormRequest
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
            'page' => 'required|integer',
            'perPage' => 'required|integer',
            'sortBy' => [
                'required',
                Rule::in(SortingValues::values())
            ],
            'withClosed' => 'required|boolean',
            'onlyClosed' => 'required|boolean',
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
