<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Account;
use App\Models\Family;
use App\Models\User;

class AccountPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Account $account): bool
    {
        $isOwner = $account->user_id === $user->id;

        /** @var Family $family */
        $family = $account->family;

        $isFamilyMember = $family && $family->members()->where('user_id', $user->id)->exists();

        return $isOwner || $isFamilyMember;
    }

    public function create(User $user, ?Family $family = null): bool
    {
        if (! $family instanceof Family) {
            return true;
        }

        return $family->owner_id === $user->id;
    }

    public function update(User $user, Account $account): bool
    {
        return $account->user_id === $user->id;
    }

    public function delete(User $user, Account $account): bool
    {
        return $this->update($user, $account);
    }
}
