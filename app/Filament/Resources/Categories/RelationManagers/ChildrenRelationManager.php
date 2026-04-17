<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Название'),
                ViewColumn::make('icon_and_color')
                    ->label('Иконка')
                    ->view('filament.tables.columns.category-icon'),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        /** @var Category $owner */
                        $owner = $this->getOwnerRecord();

                        $data['family_id'] = $owner->family_id;
                        $data['parent_id'] = $owner->id;

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
        return CategoryForm::configure($schema);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        /** @var Category $ownerRecord */
        return $ownerRecord->parent_id === null;
    }
}
