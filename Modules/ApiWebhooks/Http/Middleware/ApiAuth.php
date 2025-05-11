<?php

namespace Modules\ApiWebhooks\Http\Middleware;

use Closure;

class ApiAuth
{
    public function handle($request, Closure $next)
    {
        if ($request->getMethod() == 'OPTIONS'){
            $cors_headers = $this->getCorsHeaders();

            if ($cors_headers) {
                foreach ($cors_headers as $header_name => $header_value) {
                    header($header_name.': '.$header_value);
                }
                exit(0);
            }
        }

        $api_key = $request->api_key ?? $request->header('x_freescout_api_key') ?? '';

        if (empty($api_key) || $api_key != \ApiWebhooks::getApiKey()) {
            //abort(401, 'Not Authorized');
            $response = response()->json(['message' => 'Not Authorized'], 401);

            return $this->addCordHeaders($response);
        }

        // Add Cors headers.
        $response = $next($request);

	    return $this->addCordHeaders($response);
    }

    public function getCorsHeaders()
    {
        $headers = [];
        $cors_hosts = trim(config('apiwebhooks.cors_hosts'));

        if ($cors_hosts) {
            $headers['Access-Control-Allow-Origin'] = $cors_hosts;
            $headers['Access-Control-Allow-Headers'] = 'Content-Type,Authorization,X-FreeScout-API-Key';
            // https://github.com/freescout-helpdesk/freescout/issues/3180
            $headers['Access-Control-Allow-Methods'] = 'GET, POST, PUT, DELETE, OPTIONS';
        }

        return $headers;
    }

    public function addCordHeaders($response)
    {
    	$cors_headers = $this->getCorsHeaders();

    	if ($cors_headers) {
            foreach ($cors_headers as $header_name => $header_value) {
                $response->header($header_name, $header_value);
            }
    	}

    	// $cors_hosts_list = explode(',', $cors_hosts);

    	// foreach ($cors_hosts_list as $cors_host) {
    	// 	$cors_host = trim($cors_host);
    	// 	if ($cors_host) {
    	// 		$response->header('Access-Control-Allow-Origin', $cors_host);
    	// 		$has_hosts = true;
    	// 	}
    	// }

	    return $response;
    }
}
