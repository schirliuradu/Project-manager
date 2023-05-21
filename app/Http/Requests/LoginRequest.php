<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="password", type="string")
 * )
 */
class LoginRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ];
    }
}
