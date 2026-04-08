<?php

namespace App\Casts;

use App\Enums\Currency;
use App\Support\ValueObjects\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): Money
    {
        return new Money(
            value: (int) ($attributes['balance'] ?? 0),
            currency: Currency::from($attributes['currency'] ?? 'UAH')
        );
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if (!$value instanceof Money) {
            throw new InvalidArgumentException('The given value is not a Money instance.');
        }

        return [
            'balance' => $value->raw(),
            'currency' => $value->currency(),
        ];
    }
}
