<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Storage\Memory;
use OAuth\OAuth1\Service\AbstractService;
use OAuth\OAuth1\Signature\Signature;

class NetSuiteService
{
    protected $client;
    protected $config;

    public function __construct()
    {
        $this->client = new Client();
        $this->config = config('netsuite');
    }

    /**
     * Call a NetSuite RESTlet with the given method and data
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param array $data Data to send with the request
     * @param string|null $scriptId Override default script ID
     * @param string|null $deployId Override default deploy ID
     * @return mixed Response data (array or string)
     * @throws \Exception
     */
    public function callRestlet(string $method, array $data = [], ?string $scriptId = null, ?string $deployId = null): mixed
    {
        $scriptId = $scriptId ?? $this->config['script_id'];
        $deployId = $deployId ?? $this->config['deploy_id'];

        if (empty($scriptId) || empty($deployId)) {
            throw new \Exception('NetSuite script ID or deploy ID not configured');
        }

        $url = $this->buildRestletUrl($scriptId, $deployId);
        $headers = $this->generateAuthorizationHeaders($url, $method);

        try {
            $response = $this->client->request($method, $url, [
                'headers' => $headers,
                'json' => $data,
                'timeout' => $this->config['timeout'],
            ]);

            $content = $response->getBody()->getContents();
            
            // Try to decode as JSON, but return raw content if not valid JSON
            $decoded = json_decode($content, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $content;
        } catch (GuzzleException $e) {
            Log::error('NetSuite RESTlet error', [
                'message' => $e->getMessage(),
                'method' => $method,
                'url' => $url,
            ]);
            
            throw new \Exception('Error calling NetSuite RESTlet: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Get inventory data from NetSuite
     * 
     * @param array $params Optional parameters to pass to the RESTlet
     * @return mixed Inventory data (array or string)
     * @throws \Exception
     */
    public function getInventory(array $params = []): mixed
    {
        $result = $this->callRestlet('GET', $params);
        
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
     * Build the complete URL for the RESTlet
     *
     * @param string $scriptId NetSuite script ID
     * @param string $deployId NetSuite deploy ID
     * @return string Complete URL
     */
    protected function buildRestletUrl(string $scriptId, string $deployId): string
    {
        // Parse the base URL to ensure we don't duplicate query parameters
        $baseUrl = rtrim($this->config['base_url'], '?&');
        
        // Check if the base URL already contains a query string
        if (strpos($baseUrl, '?') !== false) {
            return $baseUrl . "&script=$scriptId&deploy=$deployId";
        } else {
            return $baseUrl . "?script=$scriptId&deploy=$deployId";
        }
    }

    /**
     * Generate OAuth 1.0 authorization headers for NetSuite
     *
     * @param string $url The request URL
     * @param string $method HTTP method
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
            $authHeaderParts[] = $key . '="' . rawurlencode($value) . '"';
        }
        
        // Add realm (account ID) parameter
        $authHeaderParts[] = 'realm="' . $auth['account_id'] . '"';
        
        // Build complete Authorization header
        $authHeader = 'OAuth ' . implode(', ', $authHeaderParts);
        
        return [
            'Authorization' => $authHeader,
            'Content-Type' => 'application/json',
        ];
    }
    
    /**
     * Generate random nonce string
     * 
     * @return string
     */
    protected function generateNonce(): string
    {
        return bin2hex(random_bytes(16));
    }
    
    /**
     * Create OAuth base string
     * 
     * @param string $method
     * @param string $url
     * @param array $params
     * @return string
     */
    protected function createBaseString(string $method, string $url, array $params): string
    {
        // Parse the URL to extract query parameters
        $urlParts = parse_url($url);
        $baseUrl = $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path'];
        
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
            $paramPairs[] = rawurlencode($key) . '=' . rawurlencode($value);
        }
        $paramString = implode('&', $paramPairs);
        
        // Combine method, URL, and params to form base string
        return strtoupper($method) . '&' . rawurlencode($baseUrl) . '&' . rawurlencode($paramString);
    }
    
    /**
     * Generate HMAC-SHA256 signature
     * 
     * @param string $baseString
     * @param string $consumerSecret
     * @param string $tokenSecret
     * @return string
     */
    protected function generateSignature(string $baseString, string $consumerSecret, string $tokenSecret): string
    {
        // Create the signature key (consumer secret + "&" + token secret)
        $key = rawurlencode($consumerSecret) . '&' . rawurlencode($tokenSecret);
        
        // Generate the signature
        return base64_encode(hash_hmac('sha256', $baseString, $key, true));
    }
}