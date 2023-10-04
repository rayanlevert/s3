<?php

namespace DisDev\S3\Tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected static \DisDev\S3\S3 $s3;

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

        self::$s3 = new \DisDev\S3\S3($accessKey, $secret, 'http://minio:9000', $region, 'test-bucket');
    }

    /**
     * Delete les objets et les buckets créés après chaque test
     */
    protected function tearDown(): void
    {
        if (!$aObjects = self::$s3->getObjects()) {
            return;
        }

        foreach ($aObjects as $bucketName => $aKeys) {
            try {
                if (!$aKeys) {
                    self::$s3->deleteBucket($bucketName);

                    continue;
                }

                foreach ($aKeys as $index => $keyName) {
                    self::$s3->deleteObject($keyName, $bucketName);

                    if ($index === array_key_last($aKeys)) {
                        self::$s3->deleteBucket($bucketName);
                    }
                }
            } catch (\Exception $e) {
            }
        }
    }
}
