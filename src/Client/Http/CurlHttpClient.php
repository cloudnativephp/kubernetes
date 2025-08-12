<?php

declare(strict_types=1);

namespace Kubernetes\Client\Http;

use Kubernetes\Exceptions\ApiException;
use RuntimeException;

/**
 * cURL HTTP client implementation.
 *
 * Provides HTTP client functionality using PHP's native cURL extension.
 * This implementation offers a lightweight alternative to Guzzle for
 * environments where external dependencies should be minimized.
 */
class CurlHttpClient implements HttpClientInterface
{
    protected string $baseUrl = '';
    protected array $defaultHeaders = [];
    protected int $timeout = 30;
    protected bool $verifySsl = true;

    /**
     * Create a new cURL HTTP client.
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException('cURL extension is required but not loaded');
        }

        $this->defaultHeaders = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];
    }

    /**
     * Send a GET request.
     *
     * @param string                $uri     The request URI
     * @param array<string, string> $headers Additional request headers
     *
     * @return HttpResponseInterface The HTTP response
     *
     * @throws ApiException If the request fails
     */
    public function get(string $uri, array $headers = []): HttpResponseInterface
    {
        return $this->sendRequest('GET', $uri, null, $headers);
    }

    /**
     * Send an HTTP request using cURL.
     *
     * @param string                $method  HTTP method
     * @param string                $uri     Request URI
     * @param string|null           $body    Request body
     * @param array<string, string> $headers Additional headers
     *
     * @return HttpResponseInterface The HTTP response
     *
     * @throws ApiException If the request fails
     */
    protected function sendRequest(string $method, string $uri, ?string $body = null, array $headers = []): HttpResponseInterface
    {
        $curl = curl_init();

        if ($curl === false) {
            throw new ApiException('Failed to initialize cURL');
        }

        try {
            $fullUri = $this->baseUrl . '/' . ltrim($uri, '/');
            $allHeaders = array_merge($this->defaultHeaders, $headers);

            // Convert headers to cURL format
            $curlHeaders = [];
            foreach ($allHeaders as $name => $value) {
                $curlHeaders[] = "{$name}: {$value}";
            }

            // Set basic cURL options
            curl_setopt_array($curl, [
                CURLOPT_URL            => $fullUri,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => true,
                CURLOPT_TIMEOUT        => $this->timeout,
                CURLOPT_CUSTOMREQUEST  => $method,
                CURLOPT_HTTPHEADER     => $curlHeaders,
                CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
                CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
            ]);

            // Set request body for methods that support it
            if ($body !== null && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
            }

            $response = curl_exec($curl);

            if ($response === false) {
                $error = curl_error($curl);
                throw new ApiException("cURL request failed: {$error}");
            }

            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

            if ($httpCode === false || $headerSize === false) {
                throw new ApiException('Failed to get cURL response information');
            }

            $headerString = substr($response, 0, $headerSize);
            $responseBody = substr($response, $headerSize);

            return new CurlHttpResponse($httpCode, $responseBody, $headerString);
        } finally {
            curl_close($curl);
        }
    }

    /**
     * Send a POST request.
     *
     * @param string                $uri     The request URI
     * @param string|null           $body    The request body
     * @param array<string, string> $headers Additional request headers
     *
     * @return HttpResponseInterface The HTTP response
     *
     * @throws ApiException If the request fails
     */
    public function post(string $uri, ?string $body = null, array $headers = []): HttpResponseInterface
    {
        return $this->sendRequest('POST', $uri, $body, $headers);
    }

    /**
     * Send a PUT request.
     *
     * @param string                $uri     The request URI
     * @param string|null           $body    The request body
     * @param array<string, string> $headers Additional request headers
     *
     * @return HttpResponseInterface The HTTP response
     *
     * @throws ApiException If the request fails
     */
    public function put(string $uri, ?string $body = null, array $headers = []): HttpResponseInterface
    {
        return $this->sendRequest('PUT', $uri, $body, $headers);
    }

    /**
     * Send a PATCH request.
     *
     * @param string                $uri     The request URI
     * @param string|null           $body    The request body
     * @param array<string, string> $headers Additional request headers
     *
     * @return HttpResponseInterface The HTTP response
     *
     * @throws ApiException If the request fails
     */
    public function patch(string $uri, ?string $body = null, array $headers = []): HttpResponseInterface
    {
        return $this->sendRequest('PATCH', $uri, $body, $headers);
    }

    /**
     * Send a DELETE request.
     *
     * @param string                $uri     The request URI
     * @param array<string, string> $headers Additional request headers
     *
     * @return HttpResponseInterface The HTTP response
     *
     * @throws ApiException If the request fails
     */
    public function delete(string $uri, array $headers = []): HttpResponseInterface
    {
        return $this->sendRequest('DELETE', $uri, null, $headers);
    }

    /**
     * Set the base URL for requests.
     *
     * @param string $baseUrl The base URL
     *
     * @return self
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = rtrim($baseUrl, '/');

        return $this;
    }

    /**
     * Set default headers for all requests.
     *
     * @param array<string, string> $headers Default headers
     *
     * @return self
     */
    public function setDefaultHeaders(array $headers): self
    {
        $this->defaultHeaders = $headers;

        return $this;
    }

    /**
     * Add a default header.
     *
     * @param string $name  Header name
     * @param string $value Header value
     *
     * @return self
     */
    public function addDefaultHeader(string $name, string $value): self
    {
        $this->defaultHeaders[$name] = $value;

        return $this;
    }

    /**
     * Set request timeout in seconds.
     *
     * @param int $timeout Timeout in seconds
     *
     * @return self
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Set SSL verification setting.
     *
     * @param bool $verify Whether to verify SSL certificates
     *
     * @return self
     */
    public function setVerifySsl(bool $verify): self
    {
        $this->verifySsl = $verify;

        return $this;
    }
}
