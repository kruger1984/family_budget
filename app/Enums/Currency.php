<?php

declare(strict_types=1);

namespace App\Enums;

enum Currency: string
{
    case UAH = 'UAH';
    case USD = 'USD';
    case EUR = 'EUR';

    public function symbol(): string
    {
        return match($this) {
            self::UAH => '₴',
            self::USD => '$',
            self::EUR => '€',
        };
    }
}
