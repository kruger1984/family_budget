<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Tables;

use App\Filament\Resources\Families\FamilyResource;
use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('parent_id'))
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->formatStateUsing(fn (string $state, $record): string => $record->parent_id ? '— '.$state : $state)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('icon_and_color')
                    ->label('Иконка')
                    ->view('filament.tables.columns.category-icon'),
                TextColumn::make('family.name')
                    ->url(fn (Category $record): ?string => $record->family->id
                        ? FamilyResource::getUrl('edit', ['record' => $record->family->id])
                        : null)
                    ->searchable(),
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
                SelectFilter::make('families')
                    ->searchable()
                    ->preload()
                    ->relationship('family', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
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
