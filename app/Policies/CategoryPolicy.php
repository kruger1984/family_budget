<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Category $category): bool
    {
        return $category->parent_id === null || $user->families()->where('families.id', $category->family_id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Category $category): bool
    {
        return $category->family_id !== null && $user->families()->where('families.id', $category->family_id)->exists();
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->update($user, $category);
    }
}
