<?php

declare(strict_types=1);

use App\Enums\Currency;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use App\Support\ValueObjects\Money;

it('lists only transactions from active family context and personal accounts', function (): void {
    $user = User::factory()->create();
    $family = Family::factory()->create();
    $user->families()->attach($family->id);

    $familyAccount = Account::factory()->create(['family_id' => $family->id]);
    $familyTransaction = Transaction::factory()->create(['account_id' => $familyAccount->id]);

    $personalAccount = Account::factory()->create(['user_id' => $user->id, 'family_id' => null]);
    $personalTransaction = Transaction::factory()->create(['account_id' => $personalAccount->id]);

    $otherTransaction = Transaction::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->withHeader('X-Family-Id', (string) $family->id)
        ->getJson('/api/transactions');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $familyTransaction->id])
        ->assertJsonFragment(['id' => $personalTransaction->id])
        ->assertJsonMissing(['id' => $otherTransaction->id]);
});

it('creates an expense transaction via API', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $user->id,
        'family_id' => null,
        'currency' => Currency::UAH,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/transactions', [
            'account_id' => $account->id,
            'amount' => 100050,
            'currency' => Currency::UAH->value,
            'type' => TransactionType::Expense->value,
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.type', TransactionType::Expense->value);

    $this->assertDatabaseHas('transactions', [
        'account_id' => $account->id,
        'user_id' => $user->id,
        'amount' => 100050,
    ]);
});

it('updates transaction amount via API', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id, 'family_id' => null]);

    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'type' => TransactionType::Expense,
        'currency' => Currency::UAH,
        'amount' => new Money(50000, Currency::UAH),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson("/api/transactions/{$transaction->id}", [
            'account_id' => $account->id,
            'type' => TransactionType::Expense->value,
            'currency' => Currency::UAH->value,
            'amount' => 80000,
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'amount' => 80000,
    ]);
});

it('forbids creating transaction for an account from another family', function (): void {
    $user = User::factory()->create();

    $otherAccount = Account::factory()->create([
        'currency' => Currency::UAH,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/transactions', [
            'account_id' => $otherAccount->id,
            'type' => TransactionType::Expense->value,
            'currency' => Currency::UAH->value,
            'amount' => 100,
        ]);

    $response->assertForbidden();
});

it('deletes transaction via API', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id, 'family_id' => null]);

    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/transactions/$transaction->id");

    $response->assertNoContent();

    $this->assertDatabaseMissing('transactions', [
        'id' => $transaction->id,
    ]);
});

it('cannot create a transaction with a parent category', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $family = Family::factory()->create(['owner_id' => $user->id]);

    $parentCategory = Category::factory()->create([
        'name' => 'Parent',
    ]);

    Category::factory()->create(['name' => 'Child', 'parent_id' => $parentCategory->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->withHeader('X-Family-Id', (string) $family->id)
        ->postJson('/api/transactions', [
            'account_id' => $account->id,
            'category_id' => $parentCategory->id,
            'amount' => 1000,
            'currency' => Currency::UAH->value,
            'type' => TransactionType::Expense->value,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['category_id']);
});
