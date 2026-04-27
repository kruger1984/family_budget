<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FamilyUser extends Pivot
{
    use HasFactory;

    protected $casts = [
        'role' => Role::class,
    ];
}
