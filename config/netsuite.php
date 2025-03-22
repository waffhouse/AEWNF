<?php

return [
    // Authentication credentials (shared across all RESTlets)
    'auth' => [
        'account_id' => env('NETSUITE_ACCOUNT_ID'),
        'consumer_key' => env('NETSUITE_CONSUMER_KEY'),
        'consumer_secret' => env('NETSUITE_CONSUMER_SECRET'),
        'token_id' => env('NETSUITE_TOKEN_ID'),
        'token_secret' => env('NETSUITE_TOKEN_SECRET'),
    ],
    
    // Base RESTlet URL
    'base_url' => env('NETSUITE_RESTLET_URL', 'https://restlets.api.netsuite.com/app/site/hosting/restlet.nl'),
    
    // Default script and deploy IDs
    'script_id' => env('NETSUITE_SCRIPT_ID'),
    'deploy_id' => env('NETSUITE_DEPLOY_ID'),
    
    // Request settings with sensible defaults
    'timeout' => env('NETSUITE_TIMEOUT', 30),
];