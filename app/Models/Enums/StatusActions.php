<?php

namespace App\Models\Enums;

enum StatusActions: string
{
    case OPEN = 'open';
    case CLOSE = 'close';

    /**
     * @return array
     */
    public static function basicValues(): array
    {
        return [self::OPEN->value, self::CLOSE->value];
    }

    /**
     * @return array
     */
    public static function allValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}