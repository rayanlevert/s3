<?php

namespace RayanLevert\S3\Http;

/** Client handling HTTP requests */
class Client
{
    private Curl $curl;

    /**
     * @param string $endpoint Base URI URL of the S3 endpoint
     *
     * @throws Exception If the endpoint URL is invalid
     */
    public function __construct(public readonly string $endpoint)
    {
        $this->curl = new Curl($this->endpoint);
    }

    /**
     * Sends a GET request to the S3 endpoint
     *
     * @param string $url The URL to send the request to
     *
     * @return Response The response from the S3 endpoint
     */
    public function get(string $url): Response
    {
        return Response::fromCurl($this->curl);
    }
}
