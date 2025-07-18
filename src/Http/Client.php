<?php

namespace RayanLevert\S3\Http;

use CurlHandle;

use function curl_init;
use function curl_setopt_array;
use function filter_var;

/** Client handling HTTP requests */
class Client
{
    private CurlHandle $curl;

    /**
     * @param string $endpoint Base URI URL of the S3 endpoint
     *
     * @throws Exception If the endpoint URL is invalid
     */
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
}
