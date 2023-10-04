<?php

namespace DisDev\S3;

/**
 * Class permettant la gestion des objets depuis/vers un stockage S3
 */
class S3
{
    /**
     * Client Guzzle géré par le SDK d'Amazon
     */
    protected \Aws\S3\S3Client $client;

    /**
     * Initialise le client S3 avec les credentials, région et endpoint de l'API depuis un array
     *
     * @param array{key: string, secret: string, endpoint: string, region: string} $array
     *
     * @throws \UnexpectedValueException Si l'array comporte des valeurs incorrectes
     */
    public static function fromArray(array $array): self
    {
        $key        = $array['key'] ?? null;
        $secret     = $array['secret'] ?? null;
        $endpoint   = $array['endpoint'] ?? null;
        $region     = $array['region'] ?? null;

        if (!is_string($key) || !is_string($secret) || !is_string($endpoint) || !is_string($region)) {
            throw new \UnexpectedValueException(
                __METHOD__ . ' : key, secret, endpoint and region must be string values'
            );
        }

        return new self($key, $secret, $endpoint, $region);
    }

    /**
     * Initialise le client S3 avec les credentials, région et endpoint de l'API
     *
     * @param string $key Access Key
     * @param string $secret Secret Access Key
     * @param string $endpoint URL de base de l'endpoint à requêter
     * @param string $region Région du server AWS
     */
    public function __construct(
        string $key,
        string $secret,
        string $endpoint,
        string $region
    ) {
        $this->client = new \Aws\S3\S3Client([
            'credentials' => [
                'key'    => $key,
                'secret' => $secret
            ],
            'endpoint' => $endpoint,
            'region'   => $region
        ]);
    }
}
