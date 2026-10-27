<?php
namespace App\Enums;

enum TaskStatus: string
{
    case COMPLETED = 'completed';
    case NOT_COMPLETED = 'not_completed';

    public function label(): string
    {
        return match($this) {
            self::COMPLETED => 'выполнена',
            self::NOT_COMPLETED => 'не выполнена',
        };
    }

    public static function fromLabel(string $label): ?self
    {
        return match(mb_strtolower($label)) {
            'выполнена' => self::COMPLETED,
            'не выполнена' => self::NOT_COMPLETED,
            default => null,
        };
    }
}
