<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transactions\Tables;

use App\Models\Transaction;
use App\Support\ValueObjects\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('account.name')
                    ->searchable()
                    ->formatStateUsing(function (string $state, Transaction $record): string {
                        if (! $record->account) {
                            return $state;
                        }

                        $balance = $record->account->balance->raw() / 100;

                        $currency = $record->account->currency->value ?? $record->account->currency;

                        return "$state ($balance $currency)";
                    }),
                TextColumn::make('amount')
                    ->formatStateUsing(fn ($state) => $state instanceof Money ? number_format($state->raw() / 100, 2) : $state)
                    ->sortable(),
                TextColumn::make('category.name')
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
