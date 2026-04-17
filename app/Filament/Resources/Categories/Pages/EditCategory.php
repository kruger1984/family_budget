<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            static::getResource()::getUrl('index') => 'Категории',
        ];

        /** @var Category $record */
        $record = $this->getRecord();

        if ($record->parent) {

            $breadcrumbs[static::getResource()::getUrl('edit', ['record' => $record->parent->id])] = $record->parent->name;
        }

        $breadcrumbs[] = 'Редактирование';

        return $breadcrumbs;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
