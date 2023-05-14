<?php

namespace App\Models\Enums;

enum Priority: string
{
    case LOWEST = 'lowest';
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case HIGHEST = 'highest';

    /**
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}