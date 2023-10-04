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
