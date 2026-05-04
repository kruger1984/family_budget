<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetTransactionsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Фильтры
            'filter' => ['nullable', 'array'],
            'filter.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'filter.account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'filter.type' => ['nullable', Rule::enum(TransactionType::class)],
            'filter.date_from' => ['nullable', 'date'],
            'filter.date_to' => ['nullable', 'date', 'after_of_equal:filter.date_from'],
            'filter.amount_min' => ['nullable', 'numeric', 'min:0'],
            'filter.amount_max' => ['nullable', 'numeric', 'gt:filter.amount_min'],

            'sort' => ['nullable', 'array'],
            'sort.field' => ['nullable', 'string', 'in:created_at,amount,type'],
            'sort.order' => ['nullable', 'string', 'in:asc,desc'],

            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
