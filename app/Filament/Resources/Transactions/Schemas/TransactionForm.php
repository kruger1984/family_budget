<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\Currency;
use App\Enums\TransactionType;
use App\Support\ValueObjects\Money;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('type')
                    ->options(TransactionType::class)
                    ->required(),
                Select::make('account_id')
                    ->relationship('account', 'name')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->formatStateUsing(function ($state) {
                        if ($state instanceof Money) {
                            return $state->raw() / 100;
                        }

                        return $state;
                    })
                    ->dehydrateStateUsing(fn ($state): int => (int) ($state * 100))
                    ->default(0),
                Select::make('currency')
                    ->options(Currency::class)
                    ->required(),
                Select::make('target_account_id')
                    ->relationship('targetAccount', 'name'),
                TextInput::make('target_amount')
                    ->required()
                    ->formatStateUsing(function ($state) {
                        if ($state instanceof Money) {
                            return $state->raw() / 100;
                        }

                        return $state;
                    })
                    ->dehydrateStateUsing(fn ($state): int => (int) ($state * 100))
                    ->default(0),
                Select::make('target_currency')
                    ->options(Currency::class),
                Select::make('category_id')
                    ->relationship('category', 'name'),
                TextInput::make('description'),
            ]);
    }
}
