<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
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

        return [
            'type' => TransactionType::Expense,
            'user_id' => User::factory(),
            'account_id' => Account::factory(),

            'target_account_id' => null,
            'target_amount' => null,
            'target_currency' => null,

            'category_id' => Category::factory(),

            'amount' => $this->faker->numberBetween(100, 10000),
            'currency' => $currency,

            'description' => $this->faker->sentence(),
        ];
    }

    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Transfer,

            'category_id' => null,

            'target_account_id' => Account::factory(),

            'target_amount' => $attributes['amount'] ?? 5000,
            'target_currency' => $attributes['currency'] ?? Currency::UAH,
        ]);
    }
}
