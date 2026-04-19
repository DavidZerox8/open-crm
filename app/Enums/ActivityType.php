<?php

namespace App\Enums;

enum ActivityType: string
{
    case Note = 'note';
    case Call = 'call';
    case Email = 'email';
    case Meeting = 'meeting';

    public function label(): string
    {
        return __('crm.activity_type.'.$this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::Note => 'document-text',
            self::Call => 'phone',
            self::Email => 'envelope',
            self::Meeting => 'calendar-days',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Note => 'zinc',
            self::Call => 'emerald',
            self::Email => 'sky',
            self::Meeting => 'violet',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case): string => $case->value, self::cases());
    }
}
