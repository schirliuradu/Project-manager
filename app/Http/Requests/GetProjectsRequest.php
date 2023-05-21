<?php

namespace App\Http\Requests;

use App\Models\Enums\SortingValues;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 *
 * @OA\Parameter(
 *     parameter="pageParameter",
 *     name="page",
 *     description="Page",
 *     required=true,
 *     in="query",
 *     @OA\Schema(
 *         type="integer"
 *     )
 * ),
 *
 * @OA\Parameter(
 *     parameter="perPageParameter",
 *     name="perPage",
 *     description="Per page items",
 *     required=true,
 *     in="query",
 *     @OA\Schema(
 *         type="integer"
 *     )
 * ),
 *
 * @OA\Parameter(
 *     parameter="sortByParameter",
 *     name="sortBy",
 *     description="Results sorting options.",
 *     required=true,
 *     in="query",
 *     @OA\Schema(
 *         type="string",
 *         enum={"alpha_desc", "alpha_asc", "create", "update"}
 *     )
 * ),
 *
 * @OA\Parameter(
 *     parameter="withClosedParameter",
 *     name="withClosed",
 *     description="Parameter through which require both open and closed projects.",
 *     required=false,
 *     in="query",
 *     @OA\Schema(
 *         type="integer",
 *         enum={0, 1}
 *     )
 * ),
 *
 * @OA\Parameter(
 *     parameter="onlyClosedParameter",
 *     name="onlyClosed",
 *     description="Parameter through which require only closed projects.",
 *     required=false,
 *     in="query",
 *     @OA\Schema(
 *         type="integer",
 *         enum={0, 1}
 *     )
 * )
 */
class GetProjectsRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
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
}
