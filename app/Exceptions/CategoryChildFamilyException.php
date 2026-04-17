<?php

declare(strict_types=1);

namespace App\Exceptions;

use InvalidArgumentException;

final class CategoryChildFamilyException extends InvalidArgumentException
{
    protected $message = 'Children family must be the same as parent family.';
}
