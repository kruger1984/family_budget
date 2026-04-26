<?php

declare(strict_types=1);

namespace App\Support\Http;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success($data = null, ?string $message = null, $meta = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => $meta,
            'message' => $message,
            'errors' => null,
        ], $status);
    }

    public static function error(string $message, int $status = 400, $errors = null): JsonResponse
    {
        return response()->json(
            ['success' => false, 'data' => null, 'meta' => null, 'message' => $message, 'errors' => $errors],
            $status
        );
    }
}
