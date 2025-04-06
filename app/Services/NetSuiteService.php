<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use OAuth\OAuth1\Signature\Signature;

class NetSuiteService
{
    protected $client;

    protected $config;

    public function __construct()
    {
        $this->client = new Client;
        $this->config = config('netsuite');
    }

    /**
     * Call a NetSuite RESTlet with the given method and data
     *
     * @param  string  $method  HTTP method (GET, POST, PUT, DELETE)
     * @param  array  $data  Data to send with the request
     * @param  string|null  $scriptId  Override default script ID
     * @param  string|null  $deployId  Override default deploy ID
     * @param  int|null  $timeout  Override default timeout (in seconds)
     * @return mixed Response data (array or string)
     *
     * @throws \Exception
     */
    public function callRestlet(string $method, array $data = [], ?string $scriptId = null, ?string $deployId = null, ?int $timeout = null): mixed
    {
        $scriptId = $scriptId ?? $this->config['script_id'];
        $deployId = $deployId ?? $this->config['deploy_id'];
        $timeout = $timeout ?? $this->config['timeout'];

        if (empty($scriptId) || empty($deployId)) {
            Log::error('NetSuite configuration error', [
                'script_id' => $scriptId,
                'deploy_id' => $deployId,
                'config' => $this->config,
            ]);
            throw new \Exception('NetSuite script ID or deploy ID not configured');
        }

        // Use a specific URL for the sales or customer RESTlet
        $customBaseUrl = null;
        if ($scriptId === '1270') {
            $customBaseUrl = $this->config['sales_restlet_url'];
        } elseif ($scriptId === $this->config['customer_script_id'] && isset($this->config['customer_restlet_url'])) {
            $customBaseUrl = $this->config['customer_restlet_url'];
        }
        $url = $this->buildRestletUrl($scriptId, $deployId, $customBaseUrl);
        $headers = $this->generateAuthorizationHeaders($url, $method);

        Log::info('Making NetSuite RESTlet request', [
            'method' => $method,
            'url' => $url,
            'script_id' => $scriptId,
            'deploy_id' => $deployId,
            'timeout' => $timeout,
            'data' => $data,
        ]);

        try {
            $response = $this->client->request($method, $url, [
                'headers' => $headers,
                'json' => $data,
                'timeout' => $timeout,
            ]);

            $content = $response->getBody()->getContents();

            // Log response for debugging
            Log::info('NetSuite RESTlet response', [
                'status_code' => $response->getStatusCode(),
                'content_length' => strlen($content),
                'content_preview' => substr($content, 0, 200).(strlen($content) > 200 ? '...' : ''),
            ]);

            // Try to decode as JSON, but return raw content if not valid JSON
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                Log::info('Successfully decoded JSON response', [
                    'type' => gettype($decoded),
                    'is_array' => is_array($decoded),
                    'count' => is_array($decoded) ? count($decoded) : 'N/A',
                    'sample' => is_array($decoded) && count($decoded) > 0 ? json_encode(reset($decoded)) : 'Empty or not an array',
                ]);

                return $decoded;
            } else {
                Log::error('NetSuite response is not valid JSON', [
                    'json_error' => json_last_error_msg(),
                    'content_preview' => substr($content, 0, 500),
                ]);

                return $content;
            }
        } catch (GuzzleException $e) {
            Log::error('NetSuite RESTlet error', [
                'message' => $e->getMessage(),
                'method' => $method,
                'url' => $url,
                'script_id' => $scriptId,
                'deploy_id' => $deployId,
            ]);

            throw new \Exception('Error calling NetSuite RESTlet: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Get inventory data from NetSuite
     *
     * @param  array  $params  Optional parameters to pass to the RESTlet
     * @return mixed Inventory data (array or string)
     *
     * @throws \Exception
     */
    public function getInventory(array $params = []): mixed
    {
        // Extract timeout if it's in the params
        $timeout = null;
        if (isset($params['timeout'])) {
            $timeout = $params['timeout'];
            unset($params['timeout']);
        }

        $result = $this->callRestlet('GET', $params, null, null, $timeout);

        // Log what we got back for debugging
        Log::debug('NetSuite inventory response', ['type' => gettype($result), 'data' => $result]);

        // If we got a string but it looks like JSON, try to decode it
        if (is_string($result) && (str_starts_with(trim($result), '{') || str_starts_with(trim($result), '['))) {
            $decoded = json_decode($result, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $result;
    }

    /**
     * Get customer data from NetSuite
     *
     * @param  array  $params  Optional parameters to pass to the RESTlet
     * @return mixed Customer data (array or string)
     *
     * @throws \Exception
     */
    public function getCustomers(array $params = []): mixed
    {
        // Extract timeout if it's in the params
        $timeout = null;
        if (isset($params['timeout'])) {
            $timeout = $params['timeout'];
            unset($params['timeout']);
        }

        // Use the script ID and deploy ID for the customer RESTlet
        $scriptId = $this->config['customer_script_id'] ?? '1271';
        $deployId = $this->config['customer_deploy_id'] ?? '1';

        $result = $this->callRestlet('GET', $params, $scriptId, $deployId, $timeout);

        // Log what we got back for debugging
        Log::debug('NetSuite customer response', ['type' => gettype($result), 'data' => $result]);

        // If we got a string but it looks like JSON, try to decode it
        if (is_string($result) && (str_starts_with(trim($result), '{') || str_starts_with(trim($result), '['))) {
            $decoded = json_decode($result, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $result;
    }

    /**
     * Build the complete URL for the RESTlet
     *
     * @param  string  $scriptId  NetSuite script ID
     * @param  string  $deployId  NetSuite deploy ID
     * @param  string|null  $customBaseUrl  Optional custom base URL
     * @return string Complete URL
     */
    protected function buildRestletUrl(string $scriptId, string $deployId, ?string $customBaseUrl = null): string
    {
        // Determine which base URL to use
        $baseUrl = $customBaseUrl ?? $this->config['base_url'];

        // For sales data, use the dedicated URL if script ID matches
        if ($scriptId === '1270' && isset($this->config['sales_restlet_url'])) {
            $baseUrl = $this->config['sales_restlet_url'];
            Log::info('Using sales-specific RESTlet URL');
        }
        // For customer data, use the dedicated URL if available
        elseif ($scriptId === $this->config['customer_script_id'] && isset($this->config['customer_restlet_url'])) {
            $baseUrl = $this->config['customer_restlet_url'];
            Log::info('Using customer-specific RESTlet URL');
        }

        // Parse the base URL to ensure we don't duplicate query parameters
        $baseUrl = rtrim($baseUrl, '?&');

        // Check if the base URL already contains a query string
        if (strpos($baseUrl, '?') !== false) {
            return $baseUrl."&script=$scriptId&deploy=$deployId";
        } else {
            return $baseUrl."?script=$scriptId&deploy=$deployId";
        }
    }

    /**
     * Generate OAuth 1.0 authorization headers for NetSuite
     *
     * @param  string  $url  The request URL
     * @param  string  $method  HTTP method
     * @return array Headers including Authorization
     */
    protected function generateAuthorizationHeaders(string $url, string $method): array
    {
        $auth = $this->config['auth'];

        // Get OAuth parameters
        $nonce = $this->generateNonce();
        $timestamp = time();

        // Create OAuth parameter array (sorted alphabetically by key)
        $oauthParams = [
            'oauth_consumer_key' => $auth['consumer_key'],
            'oauth_nonce' => $nonce,
            'oauth_signature_method' => 'HMAC-SHA256',
            'oauth_timestamp' => $timestamp,
            'oauth_token' => $auth['token_id'],
            'oauth_version' => '1.0',
        ];

        // Create signature base string
        $baseString = $this->createBaseString($method, $url, $oauthParams);

        // Generate signature
        $signature = $this->generateSignature(
            $baseString,
            $auth['consumer_secret'],
            $auth['token_secret']
        );

        // Add signature to params
        $oauthParams['oauth_signature'] = $signature;

        // Build Authorization header - NetSuite format
        $authHeaderParts = [];
        foreach ($oauthParams as $key => $value) {
            $authHeaderParts[] = $key.'="'.rawurlencode($value).'"';
        }

        // Add realm (account ID) parameter
        $authHeaderParts[] = 'realm="'.$auth['account_id'].'"';

        // Build complete Authorization header
        $authHeader = 'OAuth '.implode(', ', $authHeaderParts);

        return [
            'Authorization' => $authHeader,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Generate random nonce string
     */
    protected function generateNonce(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Create OAuth base string
     */
    protected function createBaseString(string $method, string $url, array $params): string
    {
        // Parse the URL to extract query parameters
        $urlParts = parse_url($url);
        $baseUrl = $urlParts['scheme'].'://'.$urlParts['host'].$urlParts['path'];

        // Extract query parameters if they exist
        $queryParams = [];
        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $queryParams);
        }

        // NetSuite requires script and deploy params to be part of the signature
        $sigParams = array_merge($params, $queryParams);

        // Sort parameters alphabetically by key
        ksort($sigParams);

        // Build parameter string according to OAuth 1.0a spec
        $paramPairs = [];
        foreach ($sigParams as $key => $value) {
            $paramPairs[] = rawurlencode($key).'='.rawurlencode($value);
        }
        $paramString = implode('&', $paramPairs);

        // Combine method, URL, and params to form base string
        return strtoupper($method).'&'.rawurlencode($baseUrl).'&'.rawurlencode($paramString);
    }

    /**
     * Generate HMAC-SHA256 signature
     */
    protected function generateSignature(string $baseString, string $consumerSecret, string $tokenSecret): string
    {
        // Create the signature key (consumer secret + "&" + token secret)
        $key = rawurlencode($consumerSecret).'&'.rawurlencode($tokenSecret);

        // Generate the signature
        return base64_encode(hash_hmac('sha256', $baseString, $key, true));
    }
}
