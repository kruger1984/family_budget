<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use App\Support\ValueObjects\Money;
use Tests\TestCase;


class TransactionTest extends TestCase
{

    public function test_expense_transaction_decreases_balance_and_restores_on_delete(): void
    {
        // Arrange
        $user    = User::factory()->create();
        $account = Account::factory()->create([
            'user_id'  => $user->id,
            'currency' => Currency::UAH,
            'balance'  => new Money(5000, Currency::UAH),
        ]);

        // Act 1: Создаем транзакцию (Расход)
        $transaction = Transaction::factory()->create([
            'type'       => TransactionType::Expense,
            'account_id' => $account->id,
            'user_id'    => $user->id,
            'amount'     => new Money(1000, Currency::UAH),
        ]);

        // Assert 1: Проверяем, что в базу записались правильные "сырые" числа и енамы
        $this->assertDatabaseHas('transactions', [
            'id'       => $transaction->id,
            'type'     => TransactionType::Expense->value,
            'amount'   => 1000,
            'currency' => Currency::UAH->value,
        ]);

        // Assert 2: Проверяем, что баланс счета уменьшился (5000 - 1000 = 4000)
        // Используем fresh(), чтобы стянуть актуальные данные из БД
        $this->assertEquals(4000, $account->fresh()->balance->raw());

        // Act 2: Удаляем транзакцию
        $transaction->delete();

        // Assert 3: Проверяем, что транзакции больше нет
        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);

        // Assert 4: Проверяем, что баланс вернулся назад при отмене!
        $this->assertEquals(5000, $account->fresh()->balance->raw());
    }

    public function test_transfer_with_currency_conversion_updates_both_balances(): void
    {
        $user = User::factory()->create();

        $uahAccount = Account::factory()->create([
            'user_id'  => $user->id,
            'currency' => Currency::UAH,
            'balance'  => new Money(500000, Currency::UAH), // 5000.00 грн
        ]);

        $usdAccount = Account::factory()->create([
            'user_id'  => $user->id,
            'currency' => Currency::USD,
            'balance'  => new Money(0, Currency::USD), // 0.00 $
        ]);

        $transfer = Transaction::factory()->create([
            'type'    => TransactionType::Transfer,
            'user_id' => $user->id,

            'account_id' => $uahAccount->id,
            'amount'     => new Money(410000, Currency::UAH), // Ушло 4100.00

            'target_account_id' => $usdAccount->id,
            'target_amount'     => new Money(10000, Currency::USD), // Пришло 100.00
        ]);

        $this->assertEquals(90000, $uahAccount->fresh()->balance->raw());

        $this->assertEquals(10000, $usdAccount->fresh()->balance->raw());

        $this->assertEquals(41.0, $transfer->exchange_rate);
    }

    public function test_account_will_update_only_updated_parameters(): void
    {
        // Arrange
        $user    = User::factory()->create();
        $account = Account::factory()->create([
            'user_id'  => $user->id,
            'currency' => Currency::UAH,
            'balance'  => new Money(5000, Currency::UAH),
        ]);

        // Act 1: Создаем транзакцию (Расход)
        $transaction = Transaction::factory()->create([
            'type'       => TransactionType::Expense,
            'account_id' => $account->id,
            'user_id'    => $user->id,
            'amount'     => new Money(1000, Currency::UAH),
        ]);

        $transaction->update([
            'amount'     => new Money(800, Currency::UAH),
        ]);

        $this->assertEquals(4200, $account->fresh()->balance->raw());

    }
}
