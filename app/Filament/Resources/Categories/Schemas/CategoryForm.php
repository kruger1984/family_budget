<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Resources\Categories\CategoryIcons;
use App\Models\Category;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),

                Select::make('icon')
                    ->label('Иконка')
                    ->allowHtml()
                    ->searchable()
                    ->options(CategoryIcons::options())
                    ->required(),

                ColorPicker::make('color'),

                Select::make('family_id')
                    ->relationship('family', 'name')
                    ->hidden(fn ($livewire): bool => $livewire instanceof RelationManager)
                    ->disabled(fn (string $operation, $record): bool => $operation === 'edit' && $record && $record->parent_id === null)
                    ->dehydrated(fn ($livewire): bool => ! ($livewire instanceof RelationManager))
                    ->live()
                    ->afterStateUpdated(fn (Set $set): mixed => $set('parent_id', null)),

                Select::make('parent_id')
                    ->label('Родительская категория')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, ?Category $record, Get $get): Builder {
                            $query->whereNull('parent_id');

                            if ($record instanceof Category) {
                                $query->where('id', '!=', $record->id);
                            }

                            if ($familyId = $get('family_id')) {
                                $query->where('family_id', $familyId);
                            }

                            return $query;
                        }
                    )
                    ->searchable()
                    ->hidden(function (string $operation, $livewire, $record): bool {
                        if ($livewire instanceof RelationManager) {
                            return true;
                        }

                        if ($operation === 'create') {
                            return true;
                        }

                        return $record && $record->parent_id === null;
                    })
                    ->preload(),

            ]);
    }
}
