<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BaseApi
{
    /**
     * @param null $data
     * @param int|null $statusCode
     * @return JsonResponse
     */
    public function successResponse($data = null, int $statusCode= null): JsonResponse
    {
        return $this->sendResponse($statusCode ?? Response::HTTP_OK, $data);
    }

    /**
     * @param null $data
     * @param int|null $statusCode
     * @return JsonResponse
     */
    public function errorResponse($data = null, int $statusCode= null): JsonResponse
    {
        return $this->sendResponse($statusCode ?? Response::HTTP_INTERNAL_SERVER_ERROR, $data);
    }

    /**
     * @param int $statusCode
     * @param mixed|null $data
     * @return JsonResponse
     */
    private function sendResponse(int $statusCode, $data = null): JsonResponse
    {
        return response()->json([
            'code' => $statusCode,
            'status' => Response::$statusTexts[$statusCode] ?? 'Unknown',
            'data' => $data
        ], $statusCode);
    }
}
