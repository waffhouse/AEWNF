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
    'base_url' => env('NETSUITE_RESTLET_URL', 'https://3984077.restlets.api.netsuite.com/app/site/hosting/restlet.nl'),
    
    // Default script and deploy IDs (for inventory)
    'script_id' => env('NETSUITE_SCRIPT_ID'),
    'deploy_id' => env('NETSUITE_DEPLOY_ID'),
    
    // Sales data script and deploy IDs - hardcoded from the provided URL
    'sales_script_id' => env('NETSUITE_SALES_DATA_SCRIPT_ID', '1270'),
    'sales_deploy_id' => env('NETSUITE_SALES_DATA_DEPLOY_ID', '2'),
    
    // Dedicated URL for the sales RESTlet 
    'sales_restlet_url' => env('NETSUITE_SALES_RESTLET_URL', 'https://3984077.restlets.api.netsuite.com/app/site/hosting/restlet.nl'),
    
    // Request settings with sensible defaults
    'timeout' => env('NETSUITE_TIMEOUT', 300), // Increased from 30s to 300s (5 minutes) for large syncs
];