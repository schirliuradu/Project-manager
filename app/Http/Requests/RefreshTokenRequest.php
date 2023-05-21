<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @OA\Schema(
 *     schema="RefreshRequest",
 *     type="object",
 *     @OA\Property(property="token", type="string")
 * )
 */
class RefreshTokenRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'token' => 'required|string'
        ];
    }
}
