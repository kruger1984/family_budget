<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Socialite\Two\User as SocialiteUser;

it('logs in user with google', function (): void {

    $googleUser = mockSocialUser();

    Socialite::shouldReceive('driver->userFromToken')
        ->with('fake-google-token')
        ->andReturn($googleUser);

    $response = $this->postJson('/api/auth/social', [
        'provider' => 'google',
        'token' => 'fake-google-token',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.user.email', 'kruger@example.com');

    $this->assertDatabaseHas('users', ['email' => 'kruger@example.com']);
});

it('creates a new user if they do not exist', function (): void {

    expect(User::query()->count())->toBe(0);

    $googleUser = mockSocialUser();

    Socialite::shouldReceive('driver->userFromToken')
        ->with('fake-token')
        ->andReturn($googleUser);

    $this->postJson('/api/auth/social', [
        'provider' => 'google',
        'token' => 'fake-token',
    ]);

    expect(User::query()->count())->toBe(1);
    $this->assertDatabaseHas('users', ['email' => 'kruger@example.com']);
});

it('does not create a duplicate user if they already exist', function (): void {
    $existingUser = User::factory()->create([
        'email' => 'kruger@example.com',
        'name' => 'Old Name',
    ]);

    expect(User::query()->count())->toBe(1);

    $googleUser = mockSocialUser();

    Socialite::shouldReceive('driver->userFromToken')
        ->with('fake-token')
        ->andReturn($googleUser);

    $this->postJson('/api/auth/social', [
        'provider' => 'google',
        'token' => 'fake-token',
    ]);

    expect(User::query()->count())->toBe(1);

    $this->assertDatabaseHas('users', [
        'email' => 'kruger@example.com',
        'name' => 'Kruger Dev',
    ]);
});

it('returns authenticated user', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);
});

it('logs out user', function (): void {
    $user = User::factory()->create();

    $token = $user->createToken('test-token')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/auth/logout')
        ->assertNoContent();

    expect($user->tokens()->count())->toBe(0);
});

it('returns validation error for social login', function (): void {
    $response = $this->postJson('/api/auth/social', [
        'provider' => 'invalid-provider',
        'token' => '',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed',
        ]);
});


function mockSocialUser(
    string $id = 'google-id-123',
    string $email = 'kruger@example.com'
): SocialiteUser {
    $user = Mockery::mock(SocialiteUser::class);
    $user->shouldReceive('getId')->andReturn($id);
    $user->shouldReceive('getEmail')->andReturn($email);
    $user->shouldReceive('getName')->andReturn('Kruger Dev');
    $user->shouldReceive('getAvatar')->andReturn('https://avatar.url');

    return $user;
}
