<?php

namespace RayanLevert\S3;

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
     * Nom du single bucket pour les instances gérant un seul bucket
     */
    protected string $bucketName;

    /**
     * @var array<string, string[]> Objects/buckets créés
     */
    protected array $objects = [];

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
        $bucket     = $array['bucket'] ?? '';

        if (count(array_filter([$key, $secret, $endpoint, $region, $bucket], 'is_string')) !== 5) {
            throw new \UnexpectedValueException(
                __METHOD__ . ' : key, secret, endpoint, region and optional bucket must be string values'
            );
        }

        return new self($key, $secret, $endpoint, $region, $bucket);
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
        string $region,
        string $bucketName = ''
    ) {
        $this->client = new \Aws\S3\S3Client([
            'endpoint'                    => $endpoint,
            'region'                      => $region,
            'version'                     => 'latest',
            'use_path_style_endpoint'     => true,
            'use_aws_shared_config_files' => false,
            'credentials'             => [
                'key'    => $key,
                'secret' => $secret
            ]
        ]);

        $this->bucketName = $bucketName;
    }

    /**
     * Retourne le S3Client Aws
     */
    public function getClient(): \Aws\S3\S3Client
    {
        return $this->client;
    }

    /**
     * Retourne un boolean si un bucket exist
     *
     * @throws S3Exception Si le nom du bucket est mal formé
     */
    public function doesBucketExist(string $bucketName = ''): bool
    {
        return $this->client->doesBucketExist($bucketName ?: $this->bucketName);
    }

    /**
     * Retourne un boolean si un object (bucket et key) existe
     *
     * @throws S3Exception Si le nom du bucket est mal formé
     */
    public function doesObjectExist(string $keyName, string $bucketName = ''): bool
    {
        return $this->client->doesObjectExist($bucketName ?: $this->bucketName, $keyName);
    }

    /**
     * Créé un bucket (si le bucket est déjà créé, ne fait rien et continue le process)
     *
     * @throws S3Exception Si le nom du bucket est mal formé
     */
    public function createBucket(string $bucketName = ''): void
    {
        $bucketName = $bucketName ?: $this->bucketName;

        try {
            $this->client->createBucket(['Bucket' => $bucketName]);
        } catch (S3Exception $e) {
            // Si le bucket est déjà créé, ne throw pas l'exception
            if (409 === $e->getStatusCode()) {
                return;
            }

            throw $e;
        }

        $this->objects[$bucketName] = [];
    }

    /**
     * Créé ou remplace un object S3 d'un contenu dans un string
     *
     * @throws S3Exception Si le nom du bucket est mal formé/n'a pas été put
     */
    public function putObject(string $content, string $keyName, string $contentType, string $bucketName = ''): void
    {
        $bucketName = $bucketName ?: $this->bucketName;

        $this->client->putObject([
            'Bucket'      => $bucketName,
            'Key'         => $keyName,
            'Body'        => $content,
            'ContentType' => $contentType
        ]);

        $this->objects[$bucketName][] = $keyName;
    }

    /**
     * Créé ou remplace un object S3 d'un contenu d'un fichier
     *
     * @throws S3Exception Si le nom du bucket est mal formé/n'a pas été put
     * @throws \RuntimeException Si le fichier n'a pas pu être ouvert
     */
    public function putFile(string $filePath, string $keyName, string $contentType, string $bucketName = ''): void
    {
        $bucketName = $bucketName ?: $this->bucketName;

        $this->client->putObject([
            'Bucket'      => $bucketName,
            'Key'         => $keyName,
            'SourceFile'  => $filePath,
            'ContentType' => $contentType
        ]);

        $this->objects[$bucketName][] = $keyName;
    }

    /**
     * Ajout le contenu d'un dossier récursivement
     *
     * @param string $directoryPrefix String à préfixé à chaque clef (sous fichiers)
     *
     * @throws \UnexpectedValueException Si le dossier n'est pas readable
     * @throws S3Exception Si le nom du bucket est mal formé/n'a pas été put
     */
    public function putDirectory(string $directoryPath, string $directoryPrefix = null, string $bucketName = ''): void
    {
        if (!is_dir($directoryPath) || !is_readable($directoryPath)) {
            throw new \UnexpectedValueException($directoryPath . ' is not readable');
        }

        $this->client->uploadDirectory($directoryPath, $bucketName ?: $this->bucketName, $directoryPrefix);
    }

    /**
     * Retourne une instance `\Aws\Result` selon la clef et le bucket associés
     *
     * @throws S3Exception Si le bucket et/ou la clef n'existent pas
     */
    public function getObject(string $key, string $bucketName = ''): \Aws\Result
    {
        return $this->client->getObject([
            'Bucket' => $bucketName ?: $this->bucketName,
            'Key'    => $key
        ]);
    }

    /**
     * Retourne le contenu d'un fichier du S3 selon la clef et le bucket
     *
     * @throws S3Exception Si le bucket et/ou la clef n'existent pas
     * @throws \RuntimeException Si le stream n'a pas pu être lu
     */
    public function getObjectContent(string $key, string $bucketName = ''): string
    {
        $oObject = $this->getObject($key, $bucketName ?: $this->bucketName);

        return $oObject->get('Body')->getContents();
    }

    /**
     * Essaie de supprimer un bucket (si le bucket n'existe pas, ne fait rien et continue le process)
     *
     * @throws S3Exception Si le nom du bucket est mal formé ou le bucket a des objects encore présents
     *
     * @return bool true si l'objet existait, false sinon
     */
    public function deleteBucket(string $bucketName = ''): bool
    {
        try {
            $this->client->deleteBucket(['Bucket' => $bucketName ?: $this->bucketName]);
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
    public function deleteObject(string $keyName, string $bucketName = ''): bool
    {
        if (!$this->doesObjectExist($keyName, $bucketName)) {
            return false;
        }

        try {
            $this->client->deleteObject(['Bucket' => $bucketName ?: $this->bucketName, 'Key' => $keyName]);
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
     * Ajoute une clef dans l'array `objects`
     */
    public function addObjectKey(string $bucketName, string $keyName): void
    {
        $this->objects[$bucketName][] = $keyName;
    }

    /**
     * @return array<string, string[]> Retourne les buckets/objects créés (bucketName -> array de noms de clef)
     */
    public function getObjects(): array
    {
        return $this->objects;
    }

    /**
     * Set un bucketName par défault qui va être utilisé pour chaque méthode
     */
    public function setBucketName(string $bucketName): self
    {
        $this->bucketName = $bucketName;

        return $this;
    }
}
