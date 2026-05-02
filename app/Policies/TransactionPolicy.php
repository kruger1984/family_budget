<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        if ($user->id === $transaction->user_id) {
            return true;
        }

        $transaction->loadMissing('account');

        /** @var Account $account */
        $account = $transaction->account;
        $familyId = $account->family_id;

        if ($familyId) {
            return $user->families()->where('families.id', $familyId)->exists();
        }

        return false;
    }

    public function create(User $user, Account $account): bool
    {
        if (is_null($account->family_id)) {
            return $user->id === $account->user_id;
        }

        return $user->families()->where('families.id', $account->family_id)->exists();
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $this->view($user, $transaction);
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $this->view($user, $transaction);
    }
}
