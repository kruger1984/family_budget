<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Support\ValueObjects\Money;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        $this->apply($transaction);
    }

    public function deleted(Transaction $transaction): void
    {
        $this->rollback($transaction);
    }

    public function updated(Transaction $transaction): void
    {
        $originalTransaction = clone $transaction;

        $originalTransaction->amount = $transaction->getOriginal('amount');

        if ($transaction->type === TransactionType::Transfer) {
            $originalTransaction->target_amount = $transaction->getOriginal('target_amount');
        }

        $this->rollback($originalTransaction);
        $this->apply($transaction);
    }

    private function apply(Transaction $transaction): void
    {
        $account = $transaction->account;

        match ($transaction->type) {
            TransactionType::Income => $this->updateBalance($account, $transaction->amount->raw()),
            TransactionType::Expense => $this->updateBalance($account, -$transaction->amount->raw()),
            TransactionType::Transfer => (function () use ($transaction, $account): void {
                $this->updateBalance($account, -$transaction->amount->raw());
                $this->updateBalance($transaction->targetAccount, $transaction->target_amount->raw());
            })(),
        };
    }

    private function rollback(Transaction $transaction): void
    {
        $account = $transaction->account;

        match ($transaction->type) {
            TransactionType::Income => $this->updateBalance($account, -$transaction->amount->raw()),
            TransactionType::Expense => $this->updateBalance($account, $transaction->amount->raw()),
            TransactionType::Transfer => (function () use ($transaction, $account): void {
                $this->updateBalance($account, $transaction->amount->raw()); // ПЛЮС
                $this->updateBalance($transaction->targetAccount, -$transaction->target_amount->raw()); // МИНУС
            })(),
        };
    }

    private function updateBalance($account, int $cents): void
    {
        if (! $account) {
            return;
        }

        $newBalance = $account->balance->raw() + $cents;
        $account->balance = new Money($newBalance, $account->currency);
        $account->save();
    }
}
