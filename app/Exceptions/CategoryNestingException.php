<?php

declare(strict_types=1);

namespace App\Exceptions;

use InvalidArgumentException;

final class CategoryNestingException extends InvalidArgumentException
{
    protected $message = 'Maximum nesting level reached. Only one level of subcategories is allowed.';
}
