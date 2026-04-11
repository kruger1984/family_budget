<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Support\ValueObjects\Money;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currency = $this->faker->randomElement(Currency::cases());
        $amountValue = $this->faker->numberBetween(100, 10000);

        return [
            'type' => TransactionType::Expense,
            'user_id' => User::factory(),
            'account_id' => Account::factory(),

            'target_account_id' => null,
            'target_amount' => null,
            'target_currency' => null,

            'category_id' => Category::factory(),

            'amount' => new Money($amountValue, $currency),
            'currency' => $currency,

            'description' => $this->faker->sentence(),
        ];
    }

    public function transfer(): static
    {
        return $this->state(function (array $attributes) {
            $sourceCurrency = $attributes['amount'] instanceof Money
                ? $attributes['amount']->currency()
                : ($attributes['currency'] ?? Currency::UAH);

            return [
                'type' => TransactionType::Transfer,
                'category_id' => null,
                'target_account_id' => Account::factory(),

                'target_amount' => new Money(
                    $attributes['target_amount'] ?? 5000,
                    $attributes['target_currency'] ?? $sourceCurrency
                ),
                'target_currency' => $attributes['target_currency'] ?? $sourceCurrency,
            ];
        });
    }
}
