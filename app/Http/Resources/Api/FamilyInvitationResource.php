<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use App\Models\FamilyInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FamilyInvitation
 */
class FamilyInvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'expires_at' => $this->expires_at,
            'join_url' => config('app.frontend_url').'/join/'.$this->code,
        ];
    }
}
