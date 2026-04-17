<?php

declare(strict_types=1);

namespace App\Casts;

use App\Enums\Currency;
use App\Support\ValueObjects\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class MoneyCast implements CastsAttributes
{
    public function __construct(protected string $currencyField = 'currency') {}

    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        return new Money(
            value: (int) $value,
            currency: Currency::from($attributes[$this->currencyField] ?? 'UAH')
        );
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [
                $key => null,
                $this->currencyField => null,
            ];
        }

        throw_unless($value instanceof Money, InvalidArgumentException::class, 'The given value is not a Money instance.');

        return [
            $key => $value->raw(),
            $this->currencyField => $value->currency()->value,
        ];
    }
}
