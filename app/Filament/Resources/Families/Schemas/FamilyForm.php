<?php

declare(strict_types=1);

namespace App\Filament\Resources\Families\Schemas;

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
                TextInput::make('owner_id')
                    ->required()
                    ->numeric(),
            ]);
    }
}
