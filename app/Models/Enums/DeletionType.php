<?php

namespace App\Models\Enums;

/**
 * @OA\Schema(
 *     schema="DeletionTypeEnum",
 *     type="string",
 *     enum={"soft", "hard"},
 *     description="Deletion type."
 * )
 */
enum DeletionType: string
{
    case SOFT = 'soft';
    case HARD = 'hard';

    /**
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}