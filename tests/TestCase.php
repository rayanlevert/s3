<?php

namespace RayanLevert\S3\Tests;

use RayanLevert\S3\S3;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected S3 $s3;

    /** Creates a S3 instance from .env */
    protected function setUp(): void
    {
        $accessKey  = $_ENV['RUSTFS_ACCESS_KEY'] ?? null;
        $secret     = $_ENV['RUSTFS_SECRET'] ?? null;
        $region     = $_ENV['RUSTFS_REGION'] ?? null;

        if (!$accessKey || !$secret || !$region) {
            throw new \LogicException('RUSTFS_ACCESS_KEY, RUSTFS_SECRET and RUSTFS_REGION must be set from .env');
        }

        $this->s3 = new S3($accessKey, $secret, 'http://rustfs:9000', $region, 'test-bucket');
    }

    /** Deletes creates objects and/or buckets from tests after each test */
    protected function tearDown(): void
    {
        if (!$aObjects = $this->s3->objects) {
            return;
        }

        foreach ($aObjects as $bucketName => $aKeys) {
            try {
                if (!$aKeys) {
                    $this->s3->deleteBucket($bucketName);

                    continue;
                }

                foreach ($aKeys as $index => $keyName) {
                    $this->s3->deleteObject($keyName, $bucketName);

                    if ($index === array_key_last($aKeys)) {
                        $this->s3->deleteBucket($bucketName);
                    }
                }
            } catch (\Exception) {
            }
        }
    }
}
