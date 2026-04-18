<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;

enum Currency: string implements HasColor
{
    case UAH = 'UAH';
    case USD = 'USD';
    case EUR = 'EUR';

    public function symbol(): string
    {
        return match ($this) {
            self::UAH => '₴',
            self::USD => '$',
            self::EUR => '€',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::UAH => 'success',
            self::USD => 'info',
            self::EUR => 'danger',
        };
    }
}
