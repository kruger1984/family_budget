<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;

enum Role: string implements HasColor
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';

    public function getColor(): string
    {
        return match ($this) {
            self::Owner => 'success',
            self::Admin => 'danger',
            self::Member => 'info',
        };
    }
}
