<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accounts\Schemas;

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Support\ValueObjects\Money;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextColumn::make('family.name')
                    ->label('Семья')
                    ->badge()
                    ->searchable(),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                Select::make('type')
                    ->options(AccountType::class)
                    ->default('cash')
                    ->required(),
                Select::make('currency')
                    ->options(Currency::class)
                    ->default('UAH')
                    ->required(),
                TextInput::make('balance')
                    ->required()
                    ->formatStateUsing(function ($state) {
                        if ($state instanceof Money) {
                            return $state->raw() / 100;
                        }

                        return $state;
                    })
                    ->dehydrateStateUsing(function (string|int|float $state, Get $get): Money {
                        $currencyValue = $get('currency');

                        $currency = $currencyValue instanceof Currency
                            ? $currencyValue
                            : Currency::tryFrom($currencyValue) ?? Currency::UAH;

                        $amountInCents = (int) round((float) $state * 100);

                        return new Money($amountInCents, $currency);
                    })
                    ->default(0),
            ]);
    }
}
