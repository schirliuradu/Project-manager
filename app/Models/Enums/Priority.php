<?php

namespace App\Models\Enums;

/**
 * @OA\Schema(
 *     schema="PriorityEnum",
 *     type="string",
 *     enum={"low", "medium", "high", "very high"},
 *     description="Task priority."
 * )
 */
enum Priority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case VERYHIGH = 'very high';

    /**
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}