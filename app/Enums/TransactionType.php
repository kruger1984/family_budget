<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;

enum TransactionType: string implements HasColor
{
    case Expense = 'expense';
    case Income = 'income';
    case Transfer = 'transfer';

    public function getColor(): string
    {
        return match ($this) {
            self::Expense => 'danger',
            self::Income => 'success',
            self::Transfer => 'info',
        };
    }
}
