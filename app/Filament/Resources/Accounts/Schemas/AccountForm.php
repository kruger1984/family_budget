<?php

namespace App\Filament\Resources\Accounts\Schemas;

use App\Enums\AccountType;
use App\Enums\Currency;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                    ->relationship('family', 'name'),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                Select::make('type')
                    ->options(AccountType::class)
                    ->default('cash')
                    ->required(),
                Select::make('currency')
                    ->options(Currency::class)
                    ->default('USD')
                    ->required(),
                TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
