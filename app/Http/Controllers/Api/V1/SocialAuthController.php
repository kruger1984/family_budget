<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\SocialAuthAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProfileResource;
use App\Support\Http\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class SocialAuthController extends Controller
{
    public function __invoke(Request $request, SocialAuthAction $action): JsonResponse
    {
        $request->validate([
            'provider' => ['required', 'in:google,apple'],
            'token' => ['required', 'string'],
        ]);

        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver($request->provider);
            $socialUser = $driver->userFromToken($request->token);

            $user = $action->execute($socialUser, $request->provider);

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
