<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\SocialAuthAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SocialAuthRequest;
use App\Http\Resources\Api\ProfileResource;
use App\Support\Http\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class SocialAuthController extends Controller
{
    public function __invoke(SocialAuthRequest $request, SocialAuthAction $action): JsonResponse
    {
        $validated = $request->validated();

        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver($validated['provider']);
            $socialUser = $driver->userFromToken($validated['token']);

            $user = $action->execute($socialUser, $validated['provider']);

            $token = $user->createToken('mobile-app')->plainTextToken;

            return ApiResponse::success(
                data: [
                    'user' => ProfileResource::make($user),
                    'token' => $token,
                ],
                message: 'Logged in successfully'
            );
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return ApiResponse::error(
                message: 'Authentication failed',
                status: 401,
                errors: ['token' => 'Invalid social token']
            );
        }
    }
}
