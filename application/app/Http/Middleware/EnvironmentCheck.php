<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class EnvironmentCheck
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip validation if already on env error page
        if (strpos($request->getRequestUri(), 'env-error') !== false) {
            return $next($request);
        }

        // Validate cardano network
        if (empty(env('CARDANO_NETWORK'))) {
            return redirect()
                ->route(
                    'env-error',
                    ['message' => Crypt::encryptString('The CARDANO_NETWORK environment key is missing or empty')]
                );
        } else if (!in_array(env('CARDANO_NETWORK'), [NETWORK_TESTNET, NETWORK_MAINNET])) {
            return redirect()
                ->route(
                    'env-error',
                    ['message' => Crypt::encryptString(sprintf(
                        'The CARDANO_NETWORK environment value "%s" is invalid, expecting: %s',
                        env('CARDANO_NETWORK'),
                        implode(' or ', [NETWORK_TESTNET, NETWORK_MAINNET])
                    ))]
                );
        }

        // Validate blockfrost project id
        if (empty(env('BLOCKFROST_PROJECT_ID'))) {
            return redirect()
                ->route('env-error')
                ->with('error', 'The BLOCKFROST_PROJECT_ID environment key is missing or empty');
        }

        // Validation passed
        return $next($request);
    }
}
