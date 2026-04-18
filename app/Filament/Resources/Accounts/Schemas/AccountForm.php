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
                    ->label('Сім’я')
                    ->live()
                    ->requiredWithout('user_id')
                    ->disabled(fn (Get $get): bool => filled($get('user_id')))
                    ->prohibits('user_id')
                    ->validationMessages([
                        'required_without' => 'Оберіть сім’ю або користувача.',
                        'prohibits' => 'Рахунок не може належати одночасно і сім’ї, і користувачу.',
                    ]),

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Користувач')
                    ->live()
                    ->requiredWithout('family_id')
                    ->disabled(fn (Get $get): bool => filled($get('family_id')))
                    ->prohibits('family_id')
                    ->validationMessages([
                        'required_without' => 'Оберіть користувача або сім’ю.',
                        'prohibits' => 'Оберіть щось одне.',
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
