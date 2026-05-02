<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'target_account_id' => ['nullable', 'integer', 'exists:accounts,id', 'different:account_id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'type' => ['required', Rule::enum(TransactionType::class)],
            'amount' => ['required', 'integer', 'min:1'],
            'currency' => ['required', Rule::enum(Currency::class)],
            'target_amount' => ['nullable', 'integer', 'min:1'],
            'target_currency' => ['nullable', Rule::enum(Currency::class)],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
