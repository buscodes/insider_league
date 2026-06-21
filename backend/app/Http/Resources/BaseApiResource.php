<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Core\Constants\AppVersion;
use App\Core\Constants\Value;
use Illuminate\Http\JsonResponse;

final class BaseApiResource
{
    public static function success(mixed $data, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => Value::TRUE,
            'message' => $message,
            'data'    => $data,
            'meta'    => self::meta(),
        ], $status);
    }

    public static function error(string $message, mixed $data = null, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => Value::FALSE,
            'message' => $message,
            'data'    => $data,
            'meta'    => self::meta(),
        ], $status);
    }

    private static function meta(): array
    {
        return [
            'timestamp'  => now()->toIso8601String(),
            'version'    => AppVersion::API_VERSION,
            'pagination' => null,
        ];
    }

    private function __construct() {}
}
