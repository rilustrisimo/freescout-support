<?php

return [
    'name' => 'ApiWebhooks',
    'api_key_salt' => env('APIWEBHOOKS_API_KEY_SALT', ''),
    'cors_hosts' => env('APIWEBHOOKS_CORS_HOSTS', ''),
];
