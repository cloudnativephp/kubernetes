<?php

declare(strict_types=1);

namespace Kubernetes\Client\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Kubernetes\Exceptions\ApiException;

/**
 * Guzzle HTTP client implementation.
 *
 * Provides HTTP client functionality using the Guzzle HTTP library.
 * This implementation wraps Guzzle's client to provide a consistent
 * interface for Kubernetes API communication.
 */
class GuzzleHttpClient implements HttpClientInterface
{
    protected GuzzleClient $client;
    protected string $baseUrl = '';
    protected array $defaultHeaders = [];
    protected int $timeout = 30;
    protected bool $verifySsl = true;

    /**
     * Create a new Guzzle HTTP client.
     *
     * @param GuzzleClient|null $client Optional Guzzle client instance
     */
    public function __construct(?GuzzleClient $client = null)
    {
        $this->client = $client ?? new GuzzleClient();
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
     * Send an HTTP request.
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
        try {
            $options = [
                'headers' => array_merge($this->defaultHeaders, $headers),
                'timeout' => $this->timeout,
                'verify'  => $this->verifySsl,
            ];

            if ($body !== null) {
                $options['body'] = $body;
            }

            $fullUri = $this->baseUrl . '/' . ltrim($uri, '/');
            $response = $this->client->request($method, $fullUri, $options);

            return new GuzzleHttpResponse($response);
        } catch (GuzzleException $e) {
            throw new ApiException(
                "HTTP request failed: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
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
        $this->recreateClient();

        return $this;
    }

    /**
     * Recreate the Guzzle client with current configuration.
     */
    protected function recreateClient(): void
    {
        $config = [
            'timeout' => $this->timeout,
            'verify'  => $this->verifySsl,
            'headers' => $this->defaultHeaders,
        ];

        if (!empty($this->baseUrl)) {
            $config['base_uri'] = $this->baseUrl;
        }

        $this->client = new GuzzleClient($config);
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
        $this->recreateClient();

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
        $this->recreateClient();

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
        $this->recreateClient();

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
        $this->recreateClient();

        return $this;
    }
}
