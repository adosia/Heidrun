<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\PendingRequest;

/**
 * Class BlockFrostService
 * @package App\Services
 */
class BlockFrostService
{
    /**
     * @param string $requestUri
     * @return array
     * @throws Exception
     */
    public function get(string $requestUri): array
    {
        $endpoint = $this->buildEndpoint($requestUri);
        $response = $this->makeClient()->get($endpoint);
        $this->checkResponse($response);
        return $response->json();
    }

    /**
     * @param string $requestUri
     * @return string
     */
    private function buildEndpoint(string $requestUri): string
    {
        return sprintf(
            'https://cardano-%s.blockfrost.io/api/v0/%s',
            env('CARDANO_NETWORK'),
            $requestUri
        );
    }

    /**
     * @return PendingRequest
     */
    private function makeClient(): PendingRequest
    {
        return Http::withHeaders([
            'project_id' => env('BLOCKFROST_PROJECT_ID'),
        ]);
    }

    /**
     * @param Response $response
     * @throws Exception
     */
    private function checkResponse(Response $response): void
    {
        if (!$response->successful()) {
            throw new Exception(trim(sprintf(
                'BlockFrost error: #%d %s %s',
                $response->json('status_code') ?? -1,
                $response->json('error') ?? 'Unknown Error',
                $response->json('message') ?? ''
            )));
        }
    }
}
