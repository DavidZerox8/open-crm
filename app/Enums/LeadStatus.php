<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Unqualified = 'unqualified';
    case Converted = 'converted';

    public function label(): string
    {
        return __('crm.lead_status.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'sky',
            self::Contacted => 'indigo',
            self::Qualified => 'emerald',
            self::Unqualified => 'zinc',
            self::Converted => 'violet',
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
