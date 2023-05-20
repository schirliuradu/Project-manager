<?php

namespace App\Models\Enums;

/**
 * @OA\Schema(
 *     schema="StatusEnum",
 *     type="string",
 *     enum={"open", "blocked", "closed"},
 *     description="Project status values."
 * )
 */
enum Status: string
{
    case OPEN = 'open';
    case BLOCKED = 'blocked';
    case CLOSED = 'closed';

    /**
     * @return array
     */
    public static function basicValues(): array
    {
        return [self::OPEN->value, self::CLOSED->value];
    }

    /**
     * @return array
     */
    public static function allValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}