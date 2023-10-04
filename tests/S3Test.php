<?php

namespace DisDev\S3\Tests;

use DisDev\S3\S3;
use ReflectionProperty;
use UnexpectedValueException;

class S3Test extends TestCase
{
    /**
     * @test __construct
     */
    public function testConstruct(): void
    {
        $oS3 = new S3('key', 'secret', 'https://endpoint.com', 'region');

        $oS3Client = new ReflectionProperty($oS3, 'client');
        $oS3Client->setAccessible(true);

        /** @var \Aws\S3\S3Client $oS3Client */
        $oS3Client = $oS3Client->getValue($oS3);

        $this->assertSame('endpoint.com', $oS3Client->getEndpoint()->getHost());
        $this->assertSame('https', $oS3Client->getEndpoint()->getScheme());

        $this->assertSame('key', $oS3Client->getCredentials()->wait()->getAccessKeyId());
        $this->assertSame('secret', $oS3Client->getCredentials()->wait()->getSecretKey());

        $this->assertSame('region', $oS3Client->getRegion());
    }

    /**
     * @test ::fromArray()
     */
    public function testFromArray(): void
    {
        $oS3 = S3::fromArray([
            'key'       => 'key',
            'secret'    => 'secret',
            'endpoint'  => 'https://endpoint.com',
            'region'    => 'region'
        ]);

        $oS3Client = new ReflectionProperty($oS3, 'client');
        $oS3Client->setAccessible(true);

        /** @var \Aws\S3\S3Client $oS3Client */
        $oS3Client = $oS3Client->getValue($oS3);

        $this->assertSame('endpoint.com', $oS3Client->getEndpoint()->getHost());
        $this->assertSame('https', $oS3Client->getEndpoint()->getScheme());

        $this->assertSame('key', $oS3Client->getCredentials()->wait()->getAccessKeyId());
        $this->assertSame('secret', $oS3Client->getCredentials()->wait()->getSecretKey());

        $this->assertSame('region', $oS3Client->getRegion());
    }

    /**
     * @test ::fromArray() empty array
     */
    public function testFromArrayEmptyArray(): void
    {
        $this->expectException(UnexpectedValueException::class);

        S3::fromArray([]);
    }

    /**
     * @test ::fromArray() not key entry
     */
    public function testFromArrayNotKey(): void
    {
        $this->expectException(UnexpectedValueException::class);

        S3::fromArray(['secret' => 'secret', 'endpoint' => 'https://endpoint.com', 'region' => 'region']);
    }

    /**
     * @test ::fromArray() not secret entry
     */
    public function testFromArrayNotSecret(): void
    {
        $this->expectException(UnexpectedValueException::class);

        S3::fromArray(['key' => 'key', 'endpoint' => 'https://endpoint.com', 'region' => 'region']);
    }

    /**
     * @test ::fromArray() not endpoint
     */
    public function testFromArrayNotEndpoint(): void
    {
        $this->expectException(UnexpectedValueException::class);

        S3::fromArray(['key' => 'key', 'secret' => 'secret', 'region' => 'region']);
    }

    /**
     * @test ::fromArray() not region
     */
    public function testFromArrayNotRegion(): void
    {
        $this->expectException(UnexpectedValueException::class);

        S3::fromArray(['secret' => 'secret', 'key' => 'key', 'endpoint' => 'https://endpoint.com']);
    }
}
