<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accounts\Tables;

use App\Filament\Resources\Families\FamilyResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Account;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('family.name')
                    ->label('Семья')
                    ->url(
                        fn (Account $record): ?string => $record->family ? FamilyResource::getUrl(
                            'edit',
                            ['record' => $record->family->getKey()]
                        ) : null
                    )
                    ->searchable(),
                TextColumn::make('user.name')
                    ->url(
                        fn (Account $record): ?string => $record->user ? UserResource::getUrl(
                            'edit',
                            ['record' => $record->user->getKey()]
                        ) : null
                    )
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('currency')
                    ->badge()
                    ->searchable(),
                TextColumn::make('balance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
