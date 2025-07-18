<?php

namespace RayanLevert\S3\Http;

use CurlHandle;
use RayanLevert\S3\Http\Exception;

use function libxml_clear_errors;
use function libxml_use_internal_errors;
use function curl_exec;
use function curl_getinfo;
use function is_string;

/** Response from HTTP requests */
class Response
{
    /**
     * @param string $url The URL of the request
     * @param string $body The body of the response
     * @param int $statusCode The status code of the response
     * @param array $headers The headers of the response
     */
    public function __construct(
        public readonly string $url,
        public readonly string $body,
        public readonly int $statusCode,
        public readonly array $headers
    ) {}

    /**
     * Creates a Response from a cURL handle (executes the request)
     *
     * @param Curl $curl The cURL handle to get the response from
     *
     * @throws Exception If the cURL request fails
     */
    public static function fromCurl(Curl $curl): self
    {
        if (!is_string($body = $curl->exec())) {
            throw new Exception('Failed to execute cURL Request');
        }

        $aInfos = $curl->getInfo();

        return new self($aInfos['url'], $body, $aInfos['http_code'], $aInfos);
    }

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
