<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SettingService;
use App\Http\Controllers\Api\V1\BaseApi;
use Symfony\Component\HttpFoundation\Response;

class CustomApiAuth
{
    /**
     * @var SettingService $settingService
     */
    private $settingService;

    /**
     * CustomApiAuth constructor.
     * @param SettingService $settingService
     */
    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $requestApiAccessToken = $request->header('api-access-token');

        if (empty($requestApiAccessToken)) {
            return (new BaseApi())->errorResponse(
                'Api access token is missing from request header',
                Response::HTTP_BAD_REQUEST
            );
        }

        $dbApiAccessToken = $this->settingService->findByKey('api_access_token');

        if (!$dbApiAccessToken) {
            return (new BaseApi())->errorResponse(
                'Api access token not configured'
            );
        }

        if ($requestApiAccessToken !== $dbApiAccessToken->value) {
            return (new BaseApi())->errorResponse(
                'Api access token is invalid',
                Response::HTTP_FORBIDDEN
            );
        }

        return $next($request);
    }
}
