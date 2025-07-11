<?php

namespace RayanLevert\S3;

use Aws\S3\Exception\S3Exception;

use function count;
use function array_filter;
use function is_dir;
use function is_readable;

/** Amazon's SDK typed and documented wrapper class handling S3 object storages */
class S3
{
    /** Guzzle client handled by Amazon's SDK */
    public readonly \Aws\S3\S3Client $client;

    /** @var array<string, string[]> Buckets and objects created (bucket name -> name of keys) */
    public protected(set) array $objects = [];

    /**
     * Initializes S3 client with credentials, region and endpoint from an associative array
     *
     * @param array{key: string, secret: string, endpoint: string, region: string} $array
     *
     * @throws Exception If the array has some incorrect values
     */
    public static function fromArray(array $array): self
    {
        $key        = $array['key'] ?? null;
        $secret     = $array['secret'] ?? null;
        $endpoint   = $array['endpoint'] ?? null;
        $region     = $array['region'] ?? null;
        $bucket     = $array['bucket'] ?? '';

        if (count(array_filter([$key, $secret, $endpoint, $region, $bucket], 'is_string')) !== 5) {
            throw new Exception(
                __METHOD__ . ' : key, secret, endpoint, region and optional bucket must be string values'
            );
        }

        return new self($key, $secret, $endpoint, $region, $bucket);
    }

    /**
     * Initializes S3 client with credentials, region and endpoint
     *
     * @param string $key Access Key
     * @param string $secret Secret Access Key
     * @param string $endpoint Base URI URL of the S3 endpoint
     * @param string $region AWS server region
     * @param string $bucketName Name of the single bucket if only one bucket must be handled by the instance
     * @param bool|array $useAwsSharedConfigFiles Disables checking shared config files (defaut disables it)
     */
    public function __construct(
        string $key,
        string $secret,
        string $endpoint,
        string $region,
        public string $bucketName = '',
        bool|array $useAwsSharedConfigFiles = false
    ) {
        $this->client = new \Aws\S3\S3Client([
            'endpoint'                    => $endpoint,
            'region'                      => $region,
            'version'                     => 'latest',
            'use_path_style_endpoint'     => true,
            'use_aws_shared_config_files' => $useAwsSharedConfigFiles,
            'credentials'                 => [
                'key'    => $key,
                'secret' => $secret
            ]
        ]);
    }

    /**
     * Checks if a bucket exists
     *
     * @throws S3Exception If the bucket name is incorrect
     */
    public function doesBucketExist(string $bucketName = ''): bool
    {
        return $this->client->doesBucketExist($bucketName ?: $this->bucketName);
    }

    /**
     * Checks if an object exists
     *
     * @throws S3Exception If the bucket name is incorrect
     */
    public function doesObjectExist(string $keyName, string $bucketName = ''): bool
    {
        return $this->client->doesObjectExist($bucketName ?: $this->bucketName, $keyName);
    }

    /**
     * Creates a bucket (if the bucket already exists, continues the process)
     *
     * @throws S3Exception If the bucket name is incorrect
     */
    public function createBucket(string $bucketName = ''): void
    {
        $bucketName = $bucketName ?: $this->bucketName;

        try {
            $this->client->createBucket(['Bucket' => $bucketName]);
        } catch (S3Exception $e) {
            // If the bucket already exists, doesn't throw the exception
            if (409 === $e->getStatusCode()) {
                return;
            }

            throw $e;
        }

        $this->objects[$bucketName] = [];
    }

    /**
     * Creates or replace an object from a string
     *
     * @throws S3Exception If the bucket name is incorrect or the object has not been put from the request
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
     * Creates or replace an object from file contents
     *
     * @throws S3Exception If the bucket name is incorrect or the file has not been put from the request
     * @throws \RuntimeException If the file could not be opened
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
     * Adds recursively all files from a directory to a bucket
     *
     * @param string $directoryPrefix Virtual directory key prefix to add to each upload
     *
     * @throws Exception If the directory is not readable
     * @throws S3Exception If the bucket name is incorrect/n'a pas été put
     */
    public function putDirectory(string $directoryPath, ?string $directoryPrefix = null, string $bucketName = ''): void
    {
        if (!is_dir($directoryPath) || !is_readable($directoryPath)) {
            throw new Exception("$directoryPath is not readable");
        }

        $this->client->uploadDirectory($directoryPath, $bucketName ?: $this->bucketName, $directoryPrefix);
    }

    /**
     * Returns an `\Aws\Result` instance from a bucket and object key
     *
     * @throws S3Exception If the bucket or object key doesn't exist
     */
    public function getObject(string $key, string $bucketName = ''): \Aws\Result
    {
        return $this->client->getObject([
            'Bucket' => $bucketName ?: $this->bucketName,
            'Key'    => $key
        ]);
    }

    /**
     * Returns content of an object from S3 by its key name
     *
     * @throws S3Exception If the bucket or object key doesn't exist
     * @throws \RuntimeException If the resource has not been read
     */
    public function getObjectContent(string $key, string $bucketName = ''): string
    {
        $oObject = $this->getObject($key, $bucketName ?: $this->bucketName);

        return $oObject->get('Body')->getContents();
    }

    /**
     * Deletes a bucket (if it doesn't exist, continues the process)
     *
     * @throws S3Exception If the bucket name is incorrect or the bucket still has objects
     *
     * @return bool true if the bucket existed, false if not, in both cases the bucket has been deleted
     */
    public function deleteBucket(string $bucketName = ''): bool
    {
        try {
            $this->client->deleteBucket(['Bucket' => $bucketName ?: $this->bucketName]);
        } catch (S3Exception $e) {
            // If the bucket doesn't exist, doesn't throw the exception and returns false
            if (404 === $e->getStatusCode()) {
                return false;
            }

            throw $e;
        }

        return true;
    }

    /**
     * Tries to delete an object (if the bucket doesn't exist, continues the process)
     *
     * @throws S3Exception If the bucket name is incorrect
     *
     * @return bool true if the bucket existed, false if not, in both cases the bucket has been deleted
     */
    public function deleteObject(string $keyName, string $bucketName = ''): bool
    {
        if (!$this->doesObjectExist($keyName, $bucketName)) {
            return false;
        }

        try {
            $this->client->deleteObject(['Bucket' => $bucketName ?: $this->bucketName, 'Key' => $keyName]);
        } catch (S3Exception $e) {
            // If the bucket doesn't exist, doesn't throw the exception and returns false
            if (404 === $e->getStatusCode()) {
                return false;
            }

            throw $e;
        }

        return true;
    }

    /** Adds a key in the internal array */
    public function addObjectKey(string $bucketName, string $keyName): void
    {
        $this->objects[$bucketName][] = $keyName;
    }
}
