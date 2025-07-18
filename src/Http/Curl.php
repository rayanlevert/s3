<?php

namespace RayanLevert\S3\Http;

use CurlHandle;

use function curl_init;
use function curl_setopt_array;

/** cURL handle */
class Curl
{
    private CurlHandle $curl;

    public function __construct(public readonly string $endpoint)
    {
        if (!filter_var($this->endpoint, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid endpoint URL: ' . $this->endpoint);
        }

        $this->curl = curl_init($this->endpoint);

        curl_setopt_array($this->curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10
        ]);
    }

    /**
     * Executes the cURL request
     *
     * @see https://www.php.net/manual/en/function.curl-exec.php
     *
     * @codeCoverageIgnore Depends on curl_exec()
     */
    public function exec(): string|bool
    {
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