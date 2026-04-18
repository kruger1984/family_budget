<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;

enum AccountType: string implements HasColor
{
    case Cash = 'cash';
    case Card = 'card';
    case Bank = 'bank';

    public function getColor(): string
    {
        return match ($this) {
            self::Cash => 'success',
            self::Bank => 'info',
            self::Card => 'danger',
        };
    }
}
