<?php

declare(strict_types=1);

namespace App\Support\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public static function paginate(LengthAwarePaginator $paginator, mixed $data, ?string $message = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'limit' => $paginator->perPage(),
            ],
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
