<?php

declare(strict_types=1);

namespace App\Support\ValueObjects;

use App\Enums\Currency;
use App\Support\Traits\Makeable;
use Stringable;

final readonly class Money implements Stringable
{
    use Makeable;

    public function __construct(
        private int $value,
        private Currency $currency = Currency::UAH,
        private int $precision = 100
    ) {}

    public function raw(): int
    {
        return $this->value;
    }

    public function value(): float|int
    {
        return $this->value / $this->precision;
    }

    public function currency(): Currency
    {
        return $this->currency;
    }

    public function symbol(): string
    {
        return $this->currency->symbol();
    }

    public function __toString(): string
    {
        return number_format($this->value(), 2, '.', ' ').' '.$this->symbol();
    }
}
