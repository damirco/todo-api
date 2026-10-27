<?php

namespace App\Enums;

enum TaskPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'низкий',
            self::MEDIUM => 'средний',
            self::HIGH => 'высокий',
        };
    }

    public static function fromLabel(string $label): ?self
    {
        return match(mb_strtolower($label)) {
            'низкий' => self::LOW,
            'средний' => self::MEDIUM,
            'высокий' => self::HIGH,
            default => null,
        };
    }
}
