<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ParentCategoryRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $category = Category::query()->find($value);

        if (! $category) {
            return;
        }

        if ($category->parent_id === null) {
            $fail("You can't link a transaction to a parent category. Please select a subcategory.");
        }

        $activeFamily = request()->attributes->get('active_family');

        if ($category->family_id !== null && (! $activeFamily || $category->family_id !== $activeFamily->id)) {
            $fail('The selected category does not belong to the current family.');
        }
    }
}
