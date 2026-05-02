<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'avatar' => ['sometimes', 'nullable', 'string'],
            'name' => ['sometimes', 'string', 'max:255'],
            'birthday' => ['sometimes', 'nullable', 'date', 'before:today'],
            'timezone' => ['sometimes', 'string', 'max:10'],
        ];
    }
}
