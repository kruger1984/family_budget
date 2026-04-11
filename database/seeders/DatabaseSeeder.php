<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Currency;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Support\ValueObjects\Money;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::factory()->create([
            'name'  => 'Developer',
            'email' => 'dev@example.com',
            // 'family_id' => 1, // Раскомментируй, если используешь колонку в users
        ]);

        // Делаем цикл, который повторится 3 раза
        for ($i = 1; $i <= 3; $i++) {

            // 1. Создаем семью
            $family = \App\Models\Family::factory()->create([
                'name' => "Семья номер {$i}",
            ]);

            // 2. Создаем Мужа для этой семьи
            $husband = User::factory()->create([
                'name'  => "Муж (Семья {$i})",
                'email' => "husband{$i}@example.com",

                // ВАРИАНТ А: Если у тебя колонка family_id в таблице users:
                // 'family_id' => $family->id,
            ]);

            // ВАРИАНТ Б: Если у тебя сводная таблица (many-to-many):
            // $family->users()->attach($husband->id);

            // 3. Создаем Категории именно для ЭТОЙ семьи
            // (Судя по твоим прошлым логам, у тебя есть family_id в таблице categories)
            $foodCategory = Category::factory()->create([
                'name'      => 'Продукты',
                'family_id' => $family->id,
            ]);

            $salaryCategory = Category::factory()->create([
                'name'      => 'Зарплата',
                'family_id' => $family->id,
            ]);

            // 4. Создаем счет для мужа
            $account = Account::factory()->create([
                'user_id'  => $husband->id,
                'name'     => "Карта Мужа {$i}",
                'currency' => Currency::UAH,
                'balance'  => new Money(2000000, Currency::UAH), // 20 000 грн
            ]);

            // 5. Генерируем транзакции для этого счета
            // 5 расходов на Продукты
            Transaction::factory(5)->create([
                'user_id'     => $husband->id,
                'account_id'  => $account->id,
                'category_id' => $foodCategory->id,
                'type'        => TransactionType::Expense,
            ]);

            // 2 дохода (Зарплата)
            Transaction::factory(2)->create([
                'user_id'     => $husband->id,
                'account_id'  => $account->id,
                'category_id' => $salaryCategory->id,
                'type'        => TransactionType::Income,
            ]);
        }
    }
}
