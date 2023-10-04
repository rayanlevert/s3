<?php

namespace DisDev\S3;

use Aws\S3\Exception\S3Exception;

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
            'endpoint'                => $endpoint,
            'region'                  => $region,
            'version'                 => 'latest',
            'use_path_style_endpoint' => true,
            'credentials'             => [
                'key'    => $key,
                'secret' => $secret
            ]
        ]);
    }

    /**
     * Retourne un boolean si un bucket exist
     *
     * @throws S3Exception Si le nom du bucket est mal formé
     */
    public function doesBucketExist(string $bucketName): bool
    {
        return $this->client->doesBucketExist($bucketName);
    }

    /**
     * Retourne un boolean si un object (bucket et key) existe
     *
     * @throws S3Exception Si le nom du bucket est mal formé
     */
    public function doesObjectExist(string $bucketName, string $keyName): bool
    {
        return $this->client->doesObjectExist($bucketName, $keyName);
    }

    /**
     * Créé un bucket (si le bucket est déjà créé, ne fait rien et continue le process)
     *
     * @throws S3Exception Si le nom du bucket est mal formé
     */
    public function createBucket(string $bucketName): void
    {
        try {
            $this->client->createBucket(['Bucket' => $bucketName]);
        } catch (S3Exception $e) {
            // Si le bucket est déjà créé, ne throw pas l'exception
            if (409 === $e->getStatusCode()) {
                return;
            }

            throw $e;
        }
    }

    /**
     * Créé ou remplace un object S3 d'un contenu dans un string
     *
     * @throws S3Exception Si le nom du bucket est mal formé/n'a pas été put
     */
    public function putObject(string $content, string $bucketName, string $keyName, string $contentType): void
    {
        $this->client->putObject([
            'Bucket'      => $bucketName,
            'Key'         => $keyName,
            'Body'        => $content,
            'ContentType' => $contentType
        ]);
    }

    /**
     * Retourne une instance `\Aws\Result` selon la clef et le bucket associés
     *
     * @throws S3Exception Si le bucket et/ou la clef n'existent pas
     */
    public function getObject(string $bucketName, string $key): \Aws\Result
    {
        return $this->client->getObject([
            'Bucket' => $bucketName,
            'Key'    => $key
        ]);
    }

    /**
     * Retourne le contenu d'un fichier du S3 selon la clef et le bucket
     *
     * @throws S3Exception Si le bucket et/ou la clef n'existent pas
     * @throws \RuntimeException Si le stream n'a pas pu être lu
     */
    public function getObjectContent(string $bucketName, string $key): string
    {
        $oObject = $this->getObject($bucketName, $key);

        return $oObject->get('Body')->getContents();
    }

    /**
     * Essaie de supprimer un bucket (si le bucket n'existe pas, ne fait rien et continue le process)
     *
     * @throws S3Exception Si le nom du bucket est mal formé ou le bucket a des objects encore présents
     *
     * @return bool true si l'objet existait, false sinon
     */
    public function deleteBucket(string $bucketName): bool
    {
        try {
            $this->client->deleteBucket(['Bucket' => $bucketName]);
        } catch (S3Exception $e) {
            // Si le bucket n'existe pas, ne throw pas l'exception
            if (404 === $e->getStatusCode()) {
                return false;
            }

            throw $e;
        }

        return true;
    }

    /**
     * Essaie de supprimer un object (si le bucket n'existe pas, ne fait rien et continue le process)
     *
     * @throws S3Exception Si le nom du bucket est mal formé
     *
     * @return bool true si l'objet existait, false sinon
     */
    public function deleteObject(string $bucketName, string $keyName): bool
    {
        if (!$this->doesObjectExist($bucketName, $keyName)) {
            return false;
        }

        try {
            $this->client->deleteObject(['Bucket' => $bucketName, 'Key' => $keyName]);
        } catch (S3Exception $e) {
            // Si le bucket n'existe pas, ne throw pas l'exception
            if (404 === $e->getStatusCode()) {
                return false;
            }

            throw $e;
        }

        return true;
    }
}
