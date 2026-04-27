<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use App\Models\Family;
use App\Models\FamilyUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Family
 */
class FamilyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Family $family */
        $family = $this->resource;

        /** @var FamilyUser|null $pivot */
        $pivot = $family->getAttribute('pivot');

        return [
            'id' => $family->id,
            'name' => $family->name,
            'role' => $pivot?->role?->value,

            'owner' => ProfileResource::make($this->whenLoaded('owner')),
            'users' => ProfileResource::collection($this->whenLoaded('members')),
        ];
    }
}
