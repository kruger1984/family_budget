<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Family;
use App\Models\User;

class FamilyPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Family $family): bool
    {
        return $family->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Family $family): bool
    {
        return $family->owner_id === $user->id;
    }

    public function delete(User $user, Family $family): bool
    {
        return $family->owner_id === $user->id;
    }

    public function addMember(User $user, Family $family): bool
    {
        return $family->owner_id === $user->id;
    }
}
