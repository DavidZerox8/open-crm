<?php

namespace App\Enums;

enum DealStatus: string
{
    case Open = 'open';
    case Won = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return __('crm.deal_status.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'sky',
            self::Won => 'emerald',
            self::Lost => 'rose',
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
