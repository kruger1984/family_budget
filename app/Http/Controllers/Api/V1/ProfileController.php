<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProfileRequest;
use App\Http\Resources\Api\ProfileResource;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return ApiResponse::success(
            data: ProfileResource::make($user)
        );
    }

    public function update(ProfileRequest $request): JsonResponse
    {
        $request->user()->update($request->validated());

        return ApiResponse::success(
            data: ProfileResource::make($request->user()),
            message: 'Profile updated successfully'
        );
    }

    public function destroy(Request $request): Response
    {
        $request->user()->delete();

        return response()->noContent();
    }
}
