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
        $this->checkResponse($response, $requestUri);
        return $response->json();
    }

    /**
     * @param $serialisedSignedTransaction
     * @param string $requestUri
     * @return string
     * @throws Exception
     */
    public function submitTx($serialisedSignedTransaction, string $requestUri = 'tx/submit'): string
    {
        /**
         * TODO: It's embarrassing, but I don't know how to do the following in Laravel Guzzle Wrapper :|
         * I'll update this one day in the future... LK
         */

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->buildEndpoint($requestUri),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $serialisedSignedTransaction,
            CURLOPT_HTTPHEADER => [
                'project_id: ' . env('BLOCKFROST_PROJECT_ID'),
                'Content-Type: application/cbor'
            ],
        ]);
        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);

        if (isset($response['error'])) {
            throw new Exception(trim(sprintf(
                'BlockFrost api [%s] error: #%d %s %s',
                $requestUri,
                $response['status_code'] ?? -1,
                $response['error'] ?? 'Unknown Error',
                $response['message'] ?? ''
            )));
        }

        return $response;
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
     * @param array $optionalHeaders
     * @return PendingRequest
     */
    private function makeClient(array $optionalHeaders = []): PendingRequest
    {
        return Http::withHeaders(array_merge(
            [
                'project_id' => env('BLOCKFROST_PROJECT_ID'),
            ],
            $optionalHeaders
        ));
    }

    /**
     * @param Response $response
     * @param string $requestUri
     * @throws Exception
     */
    private function checkResponse(Response $response, string $requestUri): void
    {
        if (!$response->successful()) {
            throw new Exception(trim(sprintf(
                'BlockFrost api [%s] error: #%d %s %s',
                $requestUri,
                $response->json('status_code') ?? -1,
                $response->json('error') ?? 'Unknown Error',
                $response->json('message') ?? ''
            )));
        }
    }
}
