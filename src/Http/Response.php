<?php

namespace RayanLevert\S3\Http;

use RayanLevert\S3\Http\Exception;

use function libxml_clear_errors;
use function libxml_use_internal_errors;

/** Response from HTTP requests */
readonly class Response
{
    /**
     * @param string $url The URL of the request
     * @param string $body The body of the response
     * @param int $statusCode The status code of the response
     * @param array $headers The headers of the response
     */
    public function __construct(
        public string $url,
        public string $body,
        public int $statusCode,
        public array $headers
    ) {}

    /** Returns the response body as a SimpleXMLElement (standard for S3 responses) */
    public function xml(): \SimpleXMLElement
    {
        libxml_use_internal_errors(true);

        try {
            return new \SimpleXMLElement($this->body);
        } catch (\Exception $e) {
            throw new Exception('Invalid XML response: ' . $e->getMessage(), $e->getCode(), $e);
        } finally {
            libxml_clear_errors();
        }
    }
}
