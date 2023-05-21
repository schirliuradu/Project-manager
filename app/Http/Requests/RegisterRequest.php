<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

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
class RegisterRequest extends ApiRequest
{
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
}
