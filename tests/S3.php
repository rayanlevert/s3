<?php

namespace DisDev\S3\Tests;

/**
 * Class héritant de la vraie classe S3, ajoute les buckets/objets créés
 * pour les delete ensuite dans les tests unitaires
 */
class S3 extends \DisDev\S3\S3
{
    /**
     * @var array<string, string[]>
     */
    protected array $objects = [];

    public function createBucket(string $bucketName): void
    {
        parent::createBucket($bucketName);

        $this->objects[$bucketName] = [];
    }

    public function putObject(string $content, string $bucketName, string $keyName, string $contentType): void
    {
        parent::putObject($content, $bucketName, $keyName, $contentType);

        $this->objects[$bucketName][] = $keyName;
    }

    /**
     * @return array<string, string[]>
     */
    public function getObjects(): array
    {
        return $this->objects;
    }
}