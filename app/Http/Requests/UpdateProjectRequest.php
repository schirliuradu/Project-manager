<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Schema(
 *     schema="UpdateProjectRequest",
 *     type="object",
 *     @OA\Property(property="title", type="string", maxLength=255),
 *     @OA\Property(property="description", type="string")
 * )
 */
class UpdateProjectRequest extends FormRequest
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
            'title' => 'string|max:255|required_without_all:description',
            'description' => 'string|required_without_all:title'
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
