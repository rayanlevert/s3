<?php

namespace RayanLevert\S3\Tests;

use Aws\S3\Exception\S3Exception;
use PHPUnit\Framework\Attributes\{Test, TestDox};
use RayanLevert\S3\{Exception, S3};
use ReflectionProperty;
use RuntimeException;

class S3Test extends TestCase
{
    #[Test]
    #[TestDox('__construct')]
    public function construct(): void
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

    #[Test]
    #[TestDox('::fromArray()')]
    public function fromArray(): void
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

    #[Test]
    #[TestDox('::fromArray() empty array')]
    public function fromArrayEmptyArray(): void
    {
        $this->expectException(Exception::class);

        S3::fromArray([]);
    }

    #[Test]
    #[TestDox('::fromArray() not key entry')]
    public function fromArrayNotKey(): void
    {
        $this->expectException(Exception::class);

        S3::fromArray(['secret' => 'secret', 'endpoint' => 'https://endpoint.com', 'region' => 'region']);
    }

    #[Test]
    #[TestDox('::fromArray() not secret entry')]
    public function fromArrayNotSecret(): void
    {
        $this->expectException(Exception::class);

        S3::fromArray(['key' => 'key', 'endpoint' => 'https://endpoint.com', 'region' => 'region']);
    }

    #[Test]
    #[TestDox('::fromArray() not endpoint')]
    public function fromArrayNotEndpoint(): void
    {
        $this->expectException(Exception::class);

        S3::fromArray(['key' => 'key', 'secret' => 'secret', 'region' => 'region']);
    }

    #[Test]
    #[TestDox('::fromArray() not region')]
    public function fromArrayNotRegion(): void
    {
        $this->expectException(Exception::class);

        S3::fromArray(['secret' => 'secret', 'key' => 'key', 'endpoint' => 'https://endpoint.com']);
    }

    public function returnClient(): void
    {
        $oS3 = S3::fromArray([
            'key'       => 'key',
            'secret'    => 'secret',
            'endpoint'  => 'https://endpoint.com',
            'region'    => 'region'
        ]);

        $this->assertSame('Aws\S3\S3Client', $oS3->client::class, 'Class Aws\S3\S3Client ok');
    }

    #[Test]
    #[TestDox('Creating a bucket sets doesBucketExist to true')]
    public function createBucketTrue(): void
    {
        $this->s3->createBucket('test-bucket');

        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));
    }

    #[Test]
    #[TestDox('Creating a bucket with wrong name throws S3Exception')]
    public function createBucketWrongName(): void
    {
        $this->expectException(S3Exception::class);

        $this->s3->createBucket('wr°ng-buck€t');
    }

    #[Test]
    #[TestDox('Creating an already existing bucket does nothing')]
    public function createBucketAlreadyCreated(): void
    {
        $this->s3->createBucket('test-bucket');
        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));

        $this->s3->createBucket('test-bucket');
        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));
    }

    #[Test]
    #[TestDox('Creating an already existing default bucket does nothing')]
    public function createBucketAlreadyCreatedDefault(): void
    {
        $this->s3->createBucket();
        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));

        $this->s3->createBucket();
        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));
    }

    #[Test]
    #[TestDox('Deleting a bucket with wrong name throws S3Exception')]
    public function deleteBucketWrongName(): void
    {
        $this->expectException(S3Exception::class);

        $this->s3->deleteBucket('wr°ng-buck€t');
    }

    #[Test]
    #[TestDox('Deleting a non-existing bucket does nothing')]
    public function deleteBucketNoBucket(): void
    {
        $this->s3->deleteBucket('test-bucket');

        $this->assertFalse($this->s3->doesBucketExist('test-bucket'));
    }

    #[Test]
    #[TestDox('Deleting a non-existing default bucket does nothing')]
    public function deleteBucketNoBucketDefault(): void
    {
        $this->s3->deleteBucket();

        $this->assertFalse($this->s3->doesBucketExist());
    }

    #[Test]
    #[TestDox('Deleting an empty bucket succeeds')]
    public function deleteBucketPresentEmpty(): void
    {
        $this->s3->createBucket('test-bucket');
        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));

        $this->s3->deleteBucket('test-bucket');
        $this->assertFalse($this->s3->doesBucketExist('test-bucket'));
    }

    #[Test]
    #[TestDox('Adding an object succeeds')]
    public function putObjectOk(): void
    {
        $this->s3->createBucket('test-bucket');
        $this->s3->putObject('test-text', 'key.txt', 'text/plain', 'test-bucket');

        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));
        $this->assertTrue($this->s3->doesObjectExist('key.txt', 'test-bucket'));
        $this->assertSame('test-text', $this->s3->getObjectContent('key.txt', 'test-bucket'));
        $this->assertSame('test-text', $this->s3->getObject('key.txt', 'test-bucket')->get('Body')->getContents());
    }

    #[Test]
    #[TestDox('Adding an object to default bucket succeeds')]
    public function putObjectDefaultOk(): void
    {
        $this->s3->createBucket();
        $this->s3->putObject('test-text', 'key.txt', 'text/plain');

        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));
        $this->assertTrue($this->s3->doesObjectExist('key.txt'));
        $this->assertSame('test-text', $this->s3->getObjectContent('key.txt'));
        $this->assertSame('test-text', $this->s3->getObject('key.txt')->get('Body')->getContents());
    }

    #[Test]
    #[TestDox('Adding an object to non-existing bucket fails')]
    public function putObjectNonBucket(): void
    {
        $this->expectException(S3Exception::class);

        $this->s3->putObject('test', 'keyName.txt', 'text/plain', 'test-bucket');
    }

    #[Test]
    #[TestDox('Deleting an object succeeds')]
    public function deleteObjectOk(): void
    {
        $this->s3->createBucket('test-bucket');
        $this->s3->putObject('test-text', 'key.txt', 'text/plain', 'test-bucket');

        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));
        $this->assertTrue($this->s3->doesObjectExist('key.txt', 'test-bucket'));

        $this->assertTrue($this->s3->deleteObject('key.txt', 'test-bucket'));
        $this->assertFalse($this->s3->doesObjectExist('key.txt', 'test-bucket'));
    }

    #[Test]
    #[TestDox('Deleting an object from default bucket succeeds')]
    public function deleteObjectDefaultOk(): void
    {
        $this->s3->createBucket();
        $this->s3->putObject('test-text', 'key.txt', 'text/plain');

        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));
        $this->assertTrue($this->s3->doesObjectExist('key.txt'));

        $this->assertTrue($this->s3->deleteObject('key.txt'));
        $this->assertFalse($this->s3->doesObjectExist('key.txt'));
    }

    #[Test]
    #[TestDox('Deleting a non-existing object returns false')]
    public function deleteObjectDoesNotExist(): void
    {
        $this->assertFalse($this->s3->deleteObject('key.txt', 'test-bucket'));

        $this->s3->createBucket('test-bucket');

        $this->assertFalse($this->s3->deleteObject('key2.txt', 'test-bucket'));
    }

    #[Test]
    #[TestDox('Deleting a non-existing object from default bucket returns false')]
    public function deleteObjectDoesNotExistDefault(): void
    {
        $this->assertFalse($this->s3->deleteObject('key.txt'));

        $this->s3->createBucket();

        $this->assertFalse($this->s3->deleteObject('key2.txt'));
    }

    #[Test]
    #[TestDox('Getting an object from non-existing bucket fails')]
    public function getObjectNoBucket(): void
    {
        $this->expectException(S3Exception::class);

        $this->s3->getObject('key', 'test-bucket');
    }

    #[Test]
    #[TestDox('Getting an object from non-existing default bucket fails')]
    public function getObjectNoBucketDefault(): void
    {
        $this->expectException(S3Exception::class);

        $this->s3->getObject('key');
    }

    #[Test]
    #[TestDox('Getting a non-existing object fails')]
    public function getObjectNoKey(): void
    {
        $this->s3->createBucket('test-bucket');

        $this->expectException(S3Exception::class);

        $this->s3->getObject('key', 'test-bucket');
    }

    #[Test]
    #[TestDox('Getting a non-existing object from default bucket fails')]
    public function getObjectNoKeyDefault(): void
    {
        $this->s3->createBucket();

        $this->expectException(S3Exception::class);

        $this->s3->getObject('key');
    }

    #[Test]
    #[TestDox('Putting a non-existing file throws exception')]
    public function putFileDoesNotExist(): void
    {
        $this->s3->createBucket();

        $this->expectException(RuntimeException::class);

        $this->s3->putFile('file.txt', 'key.txt', 'text/plain');
    }

    #[Test]
    #[TestDox('Putting an existing file succeeds')]
    public function putFileOk(): void
    {
        $this->s3->createBucket();

        $this->s3->putFile('/app/tests/fixtures/test.txt', 'test.txt', 'text/plain');

        $this->assertTrue($this->s3->doesObjectExist('test.txt'));
        $this->assertSame('ceci est le contenu du fichier.', $this->s3->getObjectContent('test.txt'));
    }

    #[Test]
    #[TestDox('::getObjects()')]
    public function getObjects(): void
    {
        $this->assertSame([], $this->s3->getObjects());

        $this->s3->createBucket('test-bucket');
        $this->assertSame(['test-bucket' => []], $this->s3->getObjects());

        $this->s3->putObject('text', 'keyname.txt', 'text/plain', 'test-bucket');
        $this->assertSame(['test-bucket' => ['keyname.txt']], $this->s3->getObjects());

        $this->s3->putObject('text', 'keyname2.txt', 'text/plain', 'test-bucket');
        $this->assertSame(['test-bucket' => ['keyname.txt', 'keyname2.txt']], $this->s3->getObjects());
    }

    #[Test]
    #[TestDox('::getObjects() with default bucket name')]
    public function getObjectsDefault(): void
    {
        $this->assertSame([], $this->s3->setBucketName('test-bucket')->getObjects());

        $this->s3->createBucket();
        $this->assertSame(['test-bucket' => []], $this->s3->getObjects());

        $this->s3->putObject('text', 'keyname.txt', 'text/plain');
        $this->assertSame(['test-bucket' => ['keyname.txt']], $this->s3->getObjects());

        $this->s3->putObject('text', 'keyname2.txt', 'text/plain');
        $this->assertSame(['test-bucket' => ['keyname.txt', 'keyname2.txt']], $this->s3->getObjects());
    }

    #[Test]
    #[TestDox('::putDirectory() with non-existing directory')]
    public function putDirectoryNotDirectory(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('/not/a/directory is not readable');

        $this->s3->putDirectory('/not/a/directory');
    }

    #[Test]
    #[TestDox('::putDirectory with a single file')]
    public function putDirectoryOneFile(): void
    {
        $this->s3->createBucket();

        $this->s3->putDirectory('/app/tests/fixtures/directory');
        $this->assertTrue($this->s3->doesObjectExist('test.txt'));
        $this->s3->addObjectKey('test-bucket', 'test.txt');

        $this->s3->putDirectory('/app/tests/fixtures/directory', 'directory');
        $this->assertTrue($this->s3->doesObjectExist('directory/test.txt'));
        $this->s3->addObjectKey('test-bucket', 'directory/test.txt');
    }

    #[Test]
    #[TestDox('::putDirectory with multiple directories and prefix')]
    public function putDirectoryMultipleDirectoriesPrefix(): void
    {
        $this->s3->createBucket();

        $this->s3->putDirectory('/app/tests/fixtures/directories', 'directories');

        $this->assertTrue($this->s3->doesObjectExist('directories/test.txt'));
        $this->s3->addObjectKey('test-bucket', 'directories/test.txt');

        $this->assertTrue($this->s3->doesObjectExist('directories/directory1/test.txt'));
        $this->s3->addObjectKey('test-bucket', 'directories/directory1/test.txt');

        $this->assertTrue($this->s3->doesObjectExist('directories/directory2/test2.txt'));
        $this->s3->addObjectKey('test-bucket', 'directories/directory2/test2.txt');
    }

    #[Test]
    #[TestDox('::putDirectory with multiple directories without prefix')]
    public function putDirectoryMultipleDirectoriesNoPrefix(): void
    {
        $this->s3->createBucket();

        $this->s3->putDirectory('/app/tests/fixtures/directories');

        $this->assertTrue($this->s3->doesObjectExist('test.txt'));
        $this->s3->addObjectKey('test-bucket', 'test.txt');

        $this->assertTrue($this->s3->doesObjectExist('directory1/test.txt'));
        $this->s3->addObjectKey('test-bucket', 'directory1/test.txt');

        $this->assertTrue($this->s3->doesObjectExist('directory2/test2.txt'));
        $this->s3->addObjectKey('test-bucket', 'directory2/test2.txt');
    }
}
