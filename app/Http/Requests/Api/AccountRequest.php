<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Models\Account;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $activeFamily = $this->attributes->get('active_family');

        return $this->user()->can('create', [Account::class, $activeFamily]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(AccountType::class)],
            'currency' => ['required', Rule::enum(Currency::class)],
            'balance' => ['nullable', 'numeric'],
        ];
    }
}
