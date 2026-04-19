<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Overdue = 'overdue';

    public function label(): string
    {
        return __('crm.task_status.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'sky',
            self::Completed => 'emerald',
            self::Overdue => 'rose',
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
