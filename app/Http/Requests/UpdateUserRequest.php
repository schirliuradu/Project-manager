<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @OA\Parameter(
 *     parameter="user",
 *     name="user",
 *     description="User id",
 *     required=true,
 *     in="path",
 *     @OA\Schema(
 *         type="string"
 *     )
 * ),
 *
 * @OA\Schema(
 *     schema="UpdateUserRequest",
 *     type="object",
 *     @OA\Property(property="first_name", type="string", maxLength=255),
 *     @OA\Property(property="last_name", type="string", maxLength=255),
 *     @OA\Property(property="password", type="string", maxLength=24)
 * )
 */
class UpdateUserRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user' => 'required|uuid',
            'first_name' => 'string|max:255|required_without_all:last_name, password',
            'last_name' => 'string|required_without_all:first_name, password',
            'password' => 'string|required_without_all:first_name, last_name',
        ];
    }

    /**
     * @return array
     */
    public function validationData(): array
    {
        return array_merge($this->request->all(), [
            'user' => $this->route('user'),
        ]);
    }
}
