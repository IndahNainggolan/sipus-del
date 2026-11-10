<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function problem(
        int $status,
        string $title,
        string $detail,
        ?string $instance = null,
        array $extra = [],
    ): JsonResponse {
        return response()->json([
            'type' => 'about:blank',
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => $instance ?? request()->getPathInfo(),
            ...$extra,
        ], $status, ['Content-Type' => 'application/problem+json']);
    }

    protected function success(
        mixed $data,
        string $message,
        int $status = 200,
        array $headers = [],
        array $meta = [],
    ): JsonResponse {
        return response()->json([
            'data' => $data,
            'message' => $message,
            ...($meta === [] ? [] : ['meta' => $meta]),
        ], $status, $headers);
    }
}
