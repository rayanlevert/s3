<?php

namespace RayanLevert\S3\Http;

/** Client handling HTTP requests */
class Client
{
    protected Curl $curl;

    /**
     * @param string $endpoint Base URI URL of the S3 endpoint
     *
     * @throws Exception If the endpoint URL is invalid
     */
    public function __construct(string $endpoint)
    {
        $this->curl = new Curl($endpoint);
    }
}
