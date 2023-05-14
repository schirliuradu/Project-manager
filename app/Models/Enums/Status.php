<?php

namespace App\Models\Enums;

enum Status: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';

    /**
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}