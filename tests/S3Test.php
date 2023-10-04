<?php

namespace DisDev\S3\Tests;

use Aws\S3\Exception\S3Exception;
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

    /**
     * @test création d'un bucket -> doesBucketExist à true
     */
    public function testCreateBucketTrue(): void
    {
        self::$s3->createBucket('test-bucket');

        $this->assertTrue(self::$s3->doesBucketExist('test-bucket'));
    }

    /**
     * @test création d'un bucket d'un mauvais non -> S3Exception
     */
    public function testCreateBucketWrongName(): void
    {
        $this->expectException(S3Exception::class);

        self::$s3->createBucket('wr°ng-buck€t');
    }

    /**
     * @test création d'un bucket déjà créé -> ne fait rien
     */
    public function testCreateBucketAlreadyCreated(): void
    {
        self::$s3->createBucket('test-bucket');
        $this->assertTrue(self::$s3->doesBucketExist('test-bucket'));

        self::$s3->createBucket('test-bucket');
        $this->assertTrue(self::$s3->doesBucketExist('test-bucket'));
    }

    /**
     * @test suppression d'un bucket d'un mauvais non -> S3Exception
     */
    public function testDeleteBucketWrongName(): void
    {
        $this->expectException(S3Exception::class);

        self::$s3->deleteBucket('wr°ng-buck€t');
    }

    /**
     * @test suppression d'un bucket n'existant pas -> on ne fait rien
     */
    public function testDeleteBucketNoBucket(): void
    {
        self::$s3->deleteBucket('test-bucket');

        $this->assertFalse(self::$s3->doesBucketExist('test-bucket'));
    }

    /**
     * @test suppression d'un bucket vide -> OK
     */
    public function testDeleteBucketPresentEmpty(): void
    {
        self::$s3->createBucket('test-bucket');
        $this->assertTrue(self::$s3->doesBucketExist('test-bucket'));

        self::$s3->deleteBucket('test-bucket');
        $this->assertFalse(self::$s3->doesBucketExist('test-bucket'));
    }

    /**
     * @test ajout d'un object OK
     */
    public function testPutObjectOk(): void
    {
        self::$s3->createBucket('test-bucket');
        self::$s3->putObject('test-text', 'test-bucket', 'key.txt', 'text/plain');

        $this->assertTrue(self::$s3->doesBucketExist('test-bucket'));
        $this->assertTrue(self::$s3->doesObjectExist('test-bucket', 'key.txt'));
        $this->assertSame('test-text', self::$s3->getObjectContent('test-bucket', 'key.txt'));
        $this->assertSame('test-text', self::$s3->getObject('test-bucket', 'key.txt')->get('Body')->getContents());
    }

    /**
     * @test ajout d'un object sur un bucket n'existant pas
     */
    public function testPutObjectNonBucket(): void
    {
        $this->expectException(S3Exception::class);

        self::$s3->putObject('test', 'test-bucket', 'keyName.txt', 'text/plain');
    }

    /**
     * @test delete d'un object OK
     */
    public function testDeleteObjectOk(): void
    {
        self::$s3->createBucket('test-bucket');
        self::$s3->putObject('test-text', 'test-bucket', 'key.txt', 'text/plain');

        $this->assertTrue(self::$s3->doesBucketExist('test-bucket'));
        $this->assertTrue(self::$s3->doesObjectExist('test-bucket', 'key.txt'));

        $this->assertTrue(self::$s3->deleteObject('test-bucket', 'key.txt'));
        $this->assertFalse(self::$s3->doesObjectExist('test-bucket', 'key.txt'));
    }

    /**
     * @test delete d'un object n'existant pas -> retourne false
     */
    public function testDeleteObjectDoesNotExist(): void
    {
        $this->assertFalse(self::$s3->deleteObject('test-bucket', 'key.txt'));

        self::$s3->createBucket('test-bucket');

        $this->assertFalse(self::$s3->deleteObject('test-bucket', 'key2.txt'));
    }

    /**
     * @test get d'un object où le bucket n'existe pas
     */
    public function testGetObjectNoBucket(): void
    {
        $this->expectException(S3Exception::class);

        self::$s3->getObject('test-bucket', 'key');
    }

    /**
     * @test get d'un object où l'object n'existe pas
     */
    public function testGetObjectNoKey(): void
    {
        self::$s3->createBucket('test-bucket');
        
        $this->expectException(S3Exception::class);

        self::$s3->getObject('test-bucket', 'key');
    }
}
