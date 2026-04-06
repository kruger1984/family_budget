<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_family_with_an_owner(): void
    {
        // Arrange: Создаем пользователя, который будет владельцем
        $owner = User::factory()->create();

        // Act: Пытаемся создать семью через фабрику
        $family = Family::factory()->create([
            'name' => 'Smith Family',
            'owner_id' => $owner->id,
        ]);

        // Assert: Проверяем, что семья появилась в базе
        $this->assertDatabaseHas('families', [
            'id' => $family->id,
            'name' => 'Smith Family',
            'owner_id' => $owner->id,
        ]);
    }
}
