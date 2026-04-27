<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transactions\Tables;

use App\Enums\Currency;
use App\Models\Account;
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
                        /** @var Account|null $account */
                        $account = $record->account;

                        if (! $account) {
                            return $state;
                        }

                        /** @var Money $balanceObj */
                        $balanceObj = $account->balance;
                        $balance = $balanceObj->raw() / 100;

                        /** @var Currency|string $currencyObj */
                        $currencyObj = $account->currency;
                        $currency = $currencyObj instanceof Currency ? $currencyObj->value : (string) $currencyObj;

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
