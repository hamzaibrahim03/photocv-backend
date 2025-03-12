<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use App\Http\Responses\GenericJsonResponse;

class SignUpResponse extends GenericJsonResponse
{
    /**
     * Return a success JSON response.
     *
     * @param  string  $message
     * @param  mixed   $data
     * @param  int     $statusCode
     * @return JsonResponse
     */
    public static function success(string $message, $data = null, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Return an error JSON response.
     *
     * @param  string  $message
     * @param  string  $error
     * @param  int     $statusCode
     * @return JsonResponse
     */
    public static function error(string $message, string $error = null, int $statusCode = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $error
        ], $statusCode);
    }
}

