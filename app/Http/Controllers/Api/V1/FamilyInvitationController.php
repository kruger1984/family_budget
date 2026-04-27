<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FamilyInvitationResource;
use App\Models\Family;
use App\Models\FamilyInvitation;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FamilyInvitationController extends Controller
{
    use AuthorizesRequests;

    public function store(Family $family): JsonResponse
    {
        $this->authorize('addMember', $family);

        /** @var FamilyInvitation $invitation */
        $invitation = $family->invitations()->create([
            'code' => Str::random(8),
            'expires_at' => now()->addDays(2),
        ]);

        return ApiResponse::success(
            data: FamilyInvitationResource::make($invitation),
            status: 201
        );
    }

    public function join(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $invitation = FamilyInvitation::query()->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (! $invitation) {
            return ApiResponse::error('Invalid or expired invite code', 410);
        }

        $user = $request->user();

        if ($user->families()->where('family_id', $invitation->family_id)->exists()) {
            return ApiResponse::error('You are already a member of this family', 422);
        }

        $user->families()->attach($invitation->family_id, [
            'role' => Role::Member,
        ]);

        $invitation->delete();

        return ApiResponse::success(message: 'Joined family successfully');
    }

    public function destroy(FamilyInvitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation->family);

        $invitation->delete();

        return ApiResponse::success(message: 'Invitation revoked successfully');
    }
}
