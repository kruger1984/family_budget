<?php

declare(strict_types=1);

namespace App\Exceptions;

use InvalidArgumentException;

final class CategoryChildTypeException extends InvalidArgumentException
{
    protected $message = 'Children type must be the same type as category parent.';
}
