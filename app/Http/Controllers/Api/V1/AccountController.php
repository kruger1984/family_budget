<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AccountRequest;
use App\Http\Requests\Api\UpdateAccountRequest;
use App\Http\Resources\Api\AccountResource;
use App\Models\Account;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Account::class);

        $user = $request->user();

        $activeFamily = $request->attributes->get('active_family');

        $accounts = Account::query()
            ->where(function ($query) use ($user, $activeFamily): void {
                $query->where('user_id', $user->id)
                    ->whereNull('family_id');

                if ($activeFamily) {
                    $query->orWhere('family_id', $activeFamily->id);
                }
            })
            ->get();

        return ApiResponse::success(data: AccountResource::collection($accounts));
    }

    public function store(AccountRequest $request): JsonResponse
    {
        $activeFamily = $request->attributes->get('active_family');

        $validated = $request->validated();

        if ($activeFamily) {
            $account = Account::query()->create([
                ...$validated,
                'family_id' => $activeFamily->id,
                'user_id' => $request->user()->id,
            ]);
        } else {
            $account = Account::query()->create([
                ...$validated,
                'user_id' => $request->user()->id,
            ]);
        }

        return ApiResponse::success(
            data: AccountResource::make($account->refresh()),
            message: 'Account created successfully',
            status: 201
        );
    }

    public function show(Account $account): JsonResponse
    {
        $this->authorize('view', Account::class);

        return ApiResponse::success(
            data: AccountResource::make($account)
        );
    }

    public function update(UpdateAccountRequest $request, Account $account): JsonResponse
    {
        $validated = $request->validated();

        $account->update($validated);

        return ApiResponse::success(
            data: AccountResource::make($account),
            message: 'Account updated successfully'
        );
    }

    public function destroy(Account $account)
    {
        $this->authorize('delete', Account::class);

        $account->delete();

        return response()->noContent();
    }
}
