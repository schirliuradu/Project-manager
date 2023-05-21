<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     type="object",
 *     @OA\Property(property="first_name", type="string", maxLength=255),
 *     @OA\Property(property="last_name", type="string", maxLength=255),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="password", type="string", maxLength=24)
 * )
 */
class RegisterRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ];
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