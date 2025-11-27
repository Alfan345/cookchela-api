<?php

namespace App\Enums;

enum Language: string
{
    case ID = 'id';
    case EN = 'en';

    /**
     * Get human readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::ID => 'Bahasa Indonesia',
            self::EN => 'English',
        };
    }

    /**
     * Get all enum values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}