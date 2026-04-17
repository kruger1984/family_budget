<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Currency;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use App\Support\ValueObjects\Money;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Developer',
            'email' => 'dev@example.com',
        ]);

        for ($i = 1; $i <= 3; $i++) {

            $family = Family::factory()->create([
                'name' => "Семья номер $i",
            ]);

            $husband = User::factory()->create([
                'name' => "Муж (Семья $i)",
                'email' => "husband$i@example.com",
            ]);

            $husband->families()->attach($family->id, [
                'role' => 'admin',
            ]);

            /** @var Category $foodCategory */
            $foodCategory = Category::factory()->create([
                'name' => 'Продукты',
                'family_id' => $family->id,
            ]);

            /** @var Category $salaryCategory */
            $salaryCategory = Category::factory()->create([
                'name' => 'Зарплата',
                'family_id' => $family->id,
            ]);

            /** @var Account $account */
            $account = Account::factory()->create([
                'user_id' => $husband->id,
                'name' => "Карта Мужа $i",
                'currency' => Currency::UAH,
                'balance' => new Money(2000000, Currency::UAH),
            ]);

            Transaction::factory(5)->create([
                'user_id' => $husband->id,
                'account_id' => $account->id,
                'category_id' => $foodCategory->id,
                'type' => TransactionType::Expense,
            ]);

            Transaction::factory(2)->create([
                'user_id' => $husband->id,
                'account_id' => $account->id,
                'category_id' => $salaryCategory->id,
                'type' => TransactionType::Income,
            ]);
        }
    }
}
