<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Send a standardized success response.
     */
    public function successResponse(mixed $data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($payload, $statusCode);
    }

    /**
     * Send a standardized error response.
     */
    public function errorResponse(string $message = 'Error', int $statusCode = 400, mixed $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $statusCode);
    }
}
