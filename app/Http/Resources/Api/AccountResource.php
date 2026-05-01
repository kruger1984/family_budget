<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Account
 */
class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'currency' => $this->currency,
            'balance' => $this->balance->raw(),
            'user_id' => $this->user_id,
            'family_id' => $this->family_id,
            'family' => new FamilyResource($this->whenLoaded('family')),

            'is_personal' => is_null($this->family_id),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
