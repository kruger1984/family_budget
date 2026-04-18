<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->confirmed()
                    ->validationMessages([
                        'confirmed' => 'Паролі не збігаються. Спробуйте ще раз.',
                    ]),
                TextInput::make('password_confirmation')
                    ->password()
                    ->label('Повторіть пароль')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false),
                FileUpload::make('avatar')
                    ->disk('public')
                    ->image()
                    ->directory('user_avatar')
                    ->visibility('public'),
            ]);
    }
}
