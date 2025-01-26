<?php

namespace App\Helpers;

/**
 * Format response.
 */
class ResponseFormatter
{
    /**
     * Give success response.
     *
     * @param mixed $data
     * @param string|null $message
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = null, $message = null)
    {
        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => $message
            ],
            'data' => $data
        ], 200);
    }

    /**
     * Give error response.
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($data = null, $message = null, $code = 400)
    {
        $response = [
            'meta' => [
                'code' => $code,
                'status' => 'error',
                'message' => $message ?? 'An error occurred'
            ],
            'data' => $data
        ];

        // Log error details if in debug mode
        if (config('app.debug')) {
            \Illuminate\Support\Facades\Log::error('API Error Response', [
                'code' => $code,
                'message' => $message,
                'data' => $data
            ]);
        }

        return response()->json($response, $code);
    }
}
