<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Models\Account;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

   public function test_family_can_have_a_shared_account(): void
   {
       $family = Family::factory()->create();

       $account = Account::factory()->create([
           'family_id' => $family->id,
           'name' => 'Family Budget',
           'type' => AccountType::Cash,
           'currency' => Currency::USD,
       ]);

       $this->assertDatabaseHas('accounts', [
           'id'       => $account->id,
           'name'     => 'Family Budget',
           'currency' => Currency::USD,
       ]);
   }

   public function test_user_can_have_a_personal_account(): void
   {
       $user = User::factory()->create();

       $account = Account::factory()->create([
           'family_id' => null,
           'user_id' => $user->id,
           'name' => 'Family Budget',
           'type' => AccountType::Cash,
           'currency' => Currency::USD,
       ]);

       $this->assertDatabaseHas('accounts', [
           'id'       => $account->id,
           'user_id' => $user->id,
           'name'     => 'Family Budget',
           'currency' => Currency::USD,
       ]);
   }

   public function test_account_has_zero_balance_by_default(): void
   {
       $user = User::factory()->create();

       $account = Account::factory()->create([
           'family_id' => null,
           'user_id' => $user->id,
           'name' => 'Family Budget',
       ]);


       $this->assertDatabaseHas('accounts', [
           'id'       => $account->id,
           'balance'  => 0,
       ]);


   }
}
