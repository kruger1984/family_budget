<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transactions\Pages;

use App\Enums\TransactionType;
use App\Filament\Resources\Transactions\Schemas\TransactionForm;
use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('expense')
                ->label('Expense')
                ->icon('heroicon-o-minus-circle')
                ->color('danger')
                ->slideOver()
                ->mutateDataUsing(function (array $data): array {
                    $data['type'] = TransactionType::Expense;

                    return $data;
                })
                ->schema(TransactionForm::getSingleTransactionSchema(TransactionType::Expense)),

            CreateAction::make('income')
                ->label('Income')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->slideOver()
                ->mutateDataUsing(function (array $data): array {
                    $data['type'] = TransactionType::Income;

                    return $data;
                })
                ->schema(TransactionForm::getSingleTransactionSchema(TransactionType::Income)),

            CreateAction::make('transfer')
                ->label('Transfer')
                ->icon('heroicon-o-arrows-right-left')
                ->color('info')
                ->modalWidth('2xl')
                ->mutateDataUsing(function (array $data): array {
                    $data['type'] = TransactionType::Transfer;

                    return $data;
                })
                ->steps(TransactionForm::getTransferWizardSteps()),
        ];
    }
}
