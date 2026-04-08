<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Models\User;
use App\Support\ValueObjects\Money;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word() . ' Account',
            'user_id'   => User::factory(),
            'family_id' => null,
            'type' => fake()->randomElement(AccountType::cases()),
            'currency' => fake()->randomElement(Currency::cases()),
            'balance'   => fn (array $attributes) => Money::make(0, $attributes['currency']),
        ];
    }
}
