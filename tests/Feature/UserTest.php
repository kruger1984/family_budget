<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_user_in_the_database(): void
    {
        // Arrange (Подготовка): Создаем юзера через стандартную фабрику
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Act & Assert (Действие и Проверка):
        // Проверяем, что в таблице 'users' действительно появилась такая запись
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_can_have_an_avatar(): void
    {
        // Пытаемся создать пользователя с аватаркой
        $user = User::factory()->create([
            'avatar' => 'avatars/default.png',
        ]);

        // Проверяем, что поле сохранилось в базу
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'avatar' => 'avatars/default.png',
        ]);
    }
}
