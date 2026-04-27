<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FamilyResource;
use App\Models\Family;
use App\Support\Http\ApiResponse;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FamilyController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $families = $request->user()->families;

        return ApiResponse::success(
            data: FamilyResource::collection($families),
        );
    }

    /**
     * @throws Exception
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Family::class);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();

        /** @var Family $family */
        $family = $user->families()->create([
            'name' => $request->name,
            'owner_id' => $user->id,
        ], [
            'role' => Role::Owner,
        ]);

        $family = $user->families()->find($family->id);

        return ApiResponse::success(
            data: FamilyResource::make($family),
            message: 'Family created successfully',
            status: 201
        );
    }

    public function show(Request $request, Family $family): JsonResponse
    {
        $this->authorize('view', $family);

        $familyWithPivot = $request->user()->families()->findOrFail($family->id);

        return ApiResponse::success(
            data: FamilyResource::make($familyWithPivot)
        );
    }

    public function update(Request $request, Family $family): JsonResponse
    {
        $this->authorize('update', $family);
        $request->validate(['name' => ['required', 'string', 'max:255']]);

        $family->update(['name' => $request->name]);

        $familyWithPivot = $request->user()->families()->findOrFail($family->id);

        return ApiResponse::success(
            data: FamilyResource::make($familyWithPivot),
            message: 'Family updated successfully'
        );
    }

    public function destroy(Family $family): Response
    {
        $this->authorize('delete', $family);

        $family->delete();

        return response()->noContent();
    }
}
