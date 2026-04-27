<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FamilyController;
use App\Http\Controllers\Api\V1\FamilyInvitationController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SocialAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', fn (Request $request) => $request->user())->middleware('auth:sanctum');

Route::prefix('auth')->group(function (): void {
    Route::post('/social', SocialAuthController::class);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [ProfileController::class, 'show']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::apiResource('families', FamilyController::class);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('families/{family}/invitations', [FamilyInvitationController::class, 'store']);

    Route::post('families/join', [FamilyInvitationController::class, 'join']);

    Route::delete('family-invitations/{invitation}', [FamilyInvitationController::class, 'destroy']);
});
