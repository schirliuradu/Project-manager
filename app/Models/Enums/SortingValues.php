<?php

namespace App\Models\Enums;

/**
 * @OA\Schema(
 *     schema="SortingValues",
 *     type="string",
 *     enum={"alpha_desc", "alpha_asc", "create", "update"}
 * )
 */
enum SortingValues: string
{
    case ALPHADESC = 'alpha_desc';
    case ALPHAASC = 'alpha_asc';
    case CREATE = 'create';
    case UPDATE = 'update';

    /**
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}