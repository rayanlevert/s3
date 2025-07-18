<?php

namespace RayanLevert\S3\Http;

use CurlHandle;

use function curl_init;
use function curl_setopt_array;
use function filter_var;
use function str_ends_with;

/** cURL handle */
class Curl
{
    private CurlHandle $curl;

    /** The base URI of the S3 endpoint */
    public readonly string $baseUri;

    /** The endpoint to be requested */
    public string $endpoint = '';

    /**
     * @param string $baseUri The base URI of the S3 endpoint
     *
     * @throws Exception If the base URI is invalid
     */
    public function __construct(string $baseUri)
    {
        if (!filter_var($baseUri, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid endpoint URL: ' . $baseUri);
        }

        if (!str_ends_with($baseUri, '/')) {
            $baseUri .= '/';
        }

        $this->baseUri = $baseUri;
        $this->curl    = curl_init();

        curl_setopt_array($this->curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10
        ]);
    }

    /**
     * Executes the cURL request
     *
     * @param string $url The endpoint URL to send the request to
     *
     * @see https://www.php.net/manual/en/function.curl-exec.php
     *
     * @codeCoverageIgnore Depends on curl_exec()
     */
    public function exec(): string|bool
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->baseUri . $this->endpoint);

        return curl_exec($this->curl);
    }

    /**
     * Returns the cURL information
     *
     * @see https://www.php.net/manual/en/function.curl-getinfo.php
     *
     * @codeCoverageIgnore Depends on curl_getinfo()
     */
    public function getInfo(?int $option = null): mixed
    {
        return curl_getinfo($this->curl, $option);
    }
}