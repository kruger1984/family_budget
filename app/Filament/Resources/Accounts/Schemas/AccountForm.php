<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accounts\Schemas;

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Support\ValueObjects\Money;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('family_id')
                    ->relationship('family', 'name')
                    ->label('Family')
                    ->live()
                    ->hidden(fn ($livewire): bool => $livewire instanceof RelationManager)

                    ->requiredWithout('user_id')
                    ->disabled(fn (Get $get): bool => filled($get('user_id')))
                    ->prohibits('user_id')
                    ->validationMessages([
                        'required_without' => 'Choose family or user',
                        'prohibits' => 'An account cannot belong to both a family and a user at the same time.',
                    ]),

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('User')
                    ->live()
                    ->hidden(fn ($livewire): bool => $livewire instanceof RelationManager)
                    ->requiredWithout('family_id')
                    ->disabled(fn (Get $get): bool => filled($get('family_id')))
                    ->prohibits('family_id')
                    ->validationMessages([
                        'required_without' => 'Choose family or user',
                        'prohibits' => 'Choose one thing.',
                    ]),
                Select::make('type')
                    ->options(AccountType::class)
                    ->default('cash')
                    ->required(),
                Select::make('currency')
                    ->options(Currency::class)
                    ->default('UAH')
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->dehydrated()
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
