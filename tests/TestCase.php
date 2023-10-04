<?php

namespace DisDev\S3\Tests;

use DisDev\S3\S3;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected static \DisDev\S3\S3 $s3;

    /**
     * @var array<string, string[]> BucketName -> Keys
     */
    protected array $objectsToDelete = [];

    /**
     * Créé une instance S3 depuis le Minio en dev
     */
    public static function setUpBeforeClass(): void
    {
        $accessKey  = $_ENV['MINIO_ACCESS_KEY'] ?? null;
        $secret     = $_ENV['MINIO_SECRET'] ?? null;
        $region     = $_ENV['MINIO_REGION'] ?? null;

        if (!is_string($accessKey) || !is_string($secret) || !is_string($region)) {
            throw new \LogicException('MINIO_ACCESS_KEY, MINIO_SECRET et MINIO_REGION doivent être set depuis .env');
        }

        self::$s3 = new S3($accessKey, $secret, 'http://minio:9000', $region);
    }

    /**
     * Delete les objets et les buckets créés après chaque test
     */
    protected function tearDown(): void
    {
        if (!$this->objectsToDelete) {
            return;
        }

        foreach ($this->objectsToDelete as $bucketName => $aKeys) {
            if (!$aKeys) {
                self::$s3->deleteBucket($bucketName);

                continue;
            }

            foreach ($aKeys as $index => $keyName) {
                try {
                    self::$s3->deleteObject($bucketName, $keyName);

                    if ($index === array_key_last($aKeys)) {
                        self::$s3->deleteBucket($bucketName);
                    }
                } catch (\Exception) {
                }
            }
        }
    }
}
