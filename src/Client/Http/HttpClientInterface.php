<?php

declare(strict_types=1);

namespace Kubernetes\Client\Http;

use Kubernetes\Exceptions\ApiException;

/**
 * Interface for HTTP client implementations.
 *
 * Defines the contract for HTTP clients used to communicate with the Kubernetes API.
 * Allows for different HTTP client implementations (Guzzle, cURL, etc.) while
 * maintaining a consistent interface.
 */
interface HttpClientInterface
{
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
    public function get(string $uri, array $headers = []): HttpResponseInterface;

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
    public function post(string $uri, ?string $body = null, array $headers = []): HttpResponseInterface;

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
    public function put(string $uri, ?string $body = null, array $headers = []): HttpResponseInterface;

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
    public function patch(string $uri, ?string $body = null, array $headers = []): HttpResponseInterface;

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
    public function delete(string $uri, array $headers = []): HttpResponseInterface;

    /**
     * Set the base URL for requests.
     *
     * @param string $baseUrl The base URL
     *
     * @return self
     */
    public function setBaseUrl(string $baseUrl): self;

    /**
     * Set default headers for all requests.
     *
     * @param array<string, string> $headers Default headers
     *
     * @return self
     */
    public function setDefaultHeaders(array $headers): self;

    /**
     * Add a default header.
     *
     * @param string $name  Header name
     * @param string $value Header value
     *
     * @return self
     */
    public function addDefaultHeader(string $name, string $value): self;

    /**
     * Set request timeout in seconds.
     *
     * @param int $timeout Timeout in seconds
     *
     * @return self
     */
    public function setTimeout(int $timeout): self;

    /**
     * Set SSL verification setting.
     *
     * @param bool $verify Whether to verify SSL certificates
     *
     * @return self
     */
    public function setVerifySsl(bool $verify): self;
}
