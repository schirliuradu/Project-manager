<?php

namespace App\Models\Enums;

/**
 * @OA\Schema(
 *     schema="DifficultyEnum",
 *     type="string",
 *     enum={"1", "2", "3", "5", "8", "D", "T"},
 *     description="Task difficulty.."
 * )
 */
enum Difficulty: string
{
    case ONE = '1';
    case TWO = '2';
    case THREE = '3';
    case FIVE = '5';
    case EIGHT = '8';
    case THIRTEEN = 'D';
    case TWENTYONE = 'T';

    /**
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}