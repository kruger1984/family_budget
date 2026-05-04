<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\Currency;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetTransactionsRequest;
use App\Http\Requests\Api\TransactionRequest;
use App\Http\Resources\Api\TransactionResource;
use App\Models\Account;
use App\Models\Transaction;
use App\QueryFilters\Category;
use App\QueryFilters\DateRange;
use App\QueryFilters\Sort;
use App\QueryFilters\Type;
use App\Support\Http\ApiResponse;
use App\Support\ValueObjects\Money;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Pipeline\Pipeline;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    public function index(GetTransactionsRequest $request): JsonResponse
    {
        $user = $request->user();
        $activeFamily = $request->attributes->get('active_family');

        $query = Transaction::query()->whereHas('account', function ($query) use ($user, $activeFamily): void {
            $query->where(function ($q) use ($user, $activeFamily): void {
                $q->where('user_id', $user->id)
                    ->whereNull('family_id');

                if ($activeFamily) {
                    $q->orWhere('family_id', $activeFamily->id);
                }
            });
        });

        $transactions = resolve(Pipeline::class)
            ->send($query)
            ->through([
                Category::class,
                Type::class,
                DateRange::class,
                Sort::class,
            ])
            ->thenReturn()
            ->paginate($request->integer('limit', 20));

        return ApiResponse::paginate(
            paginator: $transactions,
            data: TransactionResource::collection($transactions)->resolve(),
        );
    }

    public function store(TransactionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $account = Account::query()->findOrFail($data['account_id']);

        $this->authorize('create', [Transaction::class, $account]);

        $data['amount'] = new Money(
            $data['amount'],
            Currency::from($data['currency'])
        );

        $transaction = $request->user()->transactions()->create($data);

        return ApiResponse::success(
            data: TransactionResource::make($transaction),
            message: 'Transaction created successfully.',
            status: 201
        );
    }

    public function show(Transaction $transaction): JsonResponse
    {
        $this->authorize('view', $transaction);

        return ApiResponse::success(
            data: TransactionResource::make($transaction)
        );
    }

    public function update(TransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $this->authorize('update', $transaction);

        $data = $request->validated();

        if (isset($data['amount'])) {
            $currency = isset($data['currency'])
                ? Currency::from($data['currency'])
                : $transaction->currency;

            $data['amount'] = new Money($data['amount'], $currency);
        }

        $transaction->update($data);

        return ApiResponse::success(
            data: TransactionResource::make($transaction),
            message: 'Transaction updated successfully',
        );
    }

    public function destroy(Transaction $transaction): Response
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return response()->noContent();
    }
}
