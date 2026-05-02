<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Transaction
 */
class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'target_account_id' => $this->target_account_id,
            'category_id' => $this->category_id,
            'type' => $this->type->value,
            'amount' => $this->amount->raw(),
            'currency' => $this->currency->value,
            'target_amount' => $this->target_amount?->raw(),
            'target_currency' => $this->target_currency?->value,
            'exchange_rate' => $this->exchange_rate,
            'description' => $this->description,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
