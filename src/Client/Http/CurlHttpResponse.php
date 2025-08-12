<?php

declare(strict_types=1);

namespace Kubernetes\Client\Http;

use JsonException;

/**
 * cURL HTTP response implementation.
 *
 * Represents an HTTP response from a cURL request, implementing the
 * HttpResponseInterface to provide a consistent interface.
 */
class CurlHttpResponse implements HttpResponseInterface
{
    protected int $statusCode;
    protected string $body;
    protected array $headers = [];

    /**
     * Create a new cURL HTTP response.
     *
     * @param int    $statusCode   The HTTP status code
     * @param string $body         The response body
     * @param string $headerString The raw header string from cURL
     */
    public function __construct(int $statusCode, string $body, string $headerString)
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->parseHeaders($headerString);
    }

    /**
     * Parse the raw header string into structured headers.
     *
     * @param string $headerString The raw header string from cURL
     */
    protected function parseHeaders(string $headerString): void
    {
        $lines = explode("\r\n", trim($headerString));

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and status lines
            if (empty($line) || strpos($line, 'HTTP/') === 0) {
                continue;
            }

            $colonPos = strpos($line, ':');
            if ($colonPos === false) {
                continue;
            }

            $name = trim(substr($line, 0, $colonPos));
            $value = trim(substr($line, $colonPos + 1));

            if (!isset($this->headers[$name])) {
                $this->headers[$name] = [];
            }

            $this->headers[$name][] = $value;
        }
    }

    /**
     * Get the response status code.
     *
     * @return int The HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the response body as a string.
     *
     * @return string The response body
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Get response headers.
     *
     * @return array<string, string[]> Response headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
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
        $lowerName = strtolower($name);

        foreach ($this->headers as $headerName => $values) {
            if (strtolower($headerName) === $lowerName) {
                return $values;
            }
        }

        return [];
    }

    /**
     * Check if the response indicates success (2xx status code).
     *
     * @return bool True if successful response
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
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
        $data = json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new JsonException('Response body is not a valid JSON object');
        }

        return $data;
    }
}
