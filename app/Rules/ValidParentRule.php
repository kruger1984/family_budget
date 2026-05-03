<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidParentRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parent = Category::query()->find($value);

        if (! $parent) {
            return;
        }

        if ($parent->parent_id !== null) {
            $fail('The selected category is already a subcategory. You cannot create a third level of nesting.');
        }

        $activeFamilyId = request()->attributes->get('active_family')?->id;

        if ($parent->family_id !== $activeFamilyId) {
            $fail('The selected parent category does not belong to the current family context.');
        }
    }
}
