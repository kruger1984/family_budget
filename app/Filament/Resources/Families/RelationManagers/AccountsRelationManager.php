<?php

declare(strict_types=1);

namespace App\Filament\Resources\Families\RelationManagers;

use App\Filament\Resources\Accounts\Schemas\AccountForm;
use App\Models\Family;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'accounts';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Account name'),
                TextColumn::make('type')->label('Type')->badge(),
                TextColumn::make('balance')->label('Balance')->money('UAH'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {

                        /** @var Family $owner */
                        $owner = $this->getOwnerRecord();

                        $data['family_id'] = $owner->id;

                        $data['user_id'] = null;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return AccountForm::configure($schema);
    }
}
