<?php

declare(strict_types=1);

namespace App\Filament\Resources\Families\Schemas;

use App\Enums\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FamilyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('owner_id')
                    ->relationship(name: 'members', titleAttribute: 'name')
                    ->label('Owner')
                    ->searchable()
                    ->preload()
                    ->pivotData([
                        'role' => Role::Owner,
                    ]),
            ]);
    }
}
