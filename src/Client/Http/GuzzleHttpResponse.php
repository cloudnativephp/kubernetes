<?php

declare(strict_types=1);

namespace Kubernetes\Client\Http;

use JsonException;
use Psr\Http\Message\ResponseInterface;

/**
 * Guzzle HTTP response wrapper.
 *
 * Wraps a Guzzle PSR-7 response to implement the HttpResponseInterface,
 * providing a consistent interface regardless of the underlying HTTP client.
 */
class GuzzleHttpResponse implements HttpResponseInterface
{
    protected ResponseInterface $response;

    /**
     * Create a new Guzzle HTTP response wrapper.
     *
     * @param ResponseInterface $response The Guzzle PSR-7 response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Get response headers.
     *
     * @return array<string, string[]> Response headers
     */
    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    /**
     * Get a specific response header.
     *
     * @param string $name Header name
     *
     * @return string[] Header values
     */
    public function getHeader(string $name): array
    {
        return $this->response->getHeader($name);
    }

    /**
     * Check if the response indicates success (2xx status code).
     *
     * @return bool True if successful response
     */
    public function isSuccessful(): bool
    {
        $statusCode = $this->getStatusCode();
        return $statusCode >= 200 && $statusCode < 300;
    }

    /**
     * Get the response status code.
     *
     * @return int The HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Get the response body as parsed JSON.
     *
     * @return array<string, mixed> Parsed JSON data
     *
     * @throws JsonException If JSON parsing fails
     */
    public function getJson(): array
    {
        $body = $this->getBody();
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new JsonException('Response body is not a valid JSON object');
        }

        return $data;
    }

    /**
     * Get the response body as a string.
     *
     * @return string The response body
     */
    public function getBody(): string
    {
        return (string) $this->response->getBody();
    }
}
