<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
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
            'name'     => 'Smith Family',
            'owner_id' => $owner->id,
        ]);

        // Assert: Проверяем, что семья появилась в базе
        $this->assertDatabaseHas('families', [
            'id'       => $family->id,
            'name'     => 'Smith Family',
            'owner_id' => $owner->id,
        ]);
    }

    public function test_user_can_join_family(): void
    {
        //Arrange
        $family = Family::factory()->create();
        $user   = User::factory()->create();

        //Act
        $family->members()->attach($user->id, ['role' => Role::Member]);

        //Assert
        $this->assertDatabaseHas('family_user', [
            'family_id' => $family->id,
            'user_id'   => $user->id,
            'role'      => Role::Member,
        ]);
    }
}
