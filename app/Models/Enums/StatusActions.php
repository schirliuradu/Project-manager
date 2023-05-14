<?php

namespace App\Models\Enums;

enum StatusActions: string
{
    case OPEN = 'open';
    case CLOSE = 'close';

    /**
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}