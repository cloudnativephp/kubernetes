<?php

declare(strict_types=1);

namespace Kubernetes\Client\Http;

use JsonException;

/**
 * Interface for HTTP response implementations.
 *
 * Provides a consistent interface for HTTP responses regardless of the underlying
 * HTTP client implementation (Guzzle, cURL, etc.).
 */
interface HttpResponseInterface
{
    /**
     * Get the response status code.
     *
     * @return int The HTTP status code
     */
    public function getStatusCode(): int;

    /**
     * Get the response body as a string.
     *
     * @return string The response body
     */
    public function getBody(): string;

    /**
     * Get response headers.
     *
     * @return array<string, string[]> Response headers
     */
    public function getHeaders(): array;

    /**
     * Get a specific response header.
     *
     * @param string $name Header name
     *
     * @return string[] Header values
     */
    public function getHeader(string $name): array;

    /**
     * Check if the response indicates success (2xx status code).
     *
     * @return bool True if successful response
     */
    public function isSuccessful(): bool;

    /**
     * Get the response body as parsed JSON.
     *
     * @return array<string, mixed> Parsed JSON data
     *
     * @throws JsonException If JSON parsing fails
     */
    public function getJson(): array;
}
