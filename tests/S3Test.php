<?php

namespace DisDev\S3\Tests;

use Aws\S3\Exception\S3Exception;
use DisDev\S3\S3;
use ReflectionProperty;
use RuntimeException;
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

    public function testReturnClient(): void
    {
        $oS3 = S3::fromArray([
            'key'       => 'key',
            'secret'    => 'secret',
            'endpoint'  => 'https://endpoint.com',
            'region'    => 'region'
        ]);

        $this->assertSame('Aws\S3\S3Client', $oS3->getClient()::class, 'Class Aws\S3\S3Client ok');
    }

    /**
     * @test création d'un bucket -> doesBucketExist à true
     */
    public function testCreateBucketTrue(): void
    {
        $this->s3->createBucket('test-bucket');

        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));
    }

    /**
     * @test création d'un bucket d'un mauvais nom -> S3Exception
     */
    public function testCreateBucketWrongName(): void
    {
        $this->expectException(S3Exception::class);

        $this->s3->createBucket('wr°ng-buck€t');
    }

    /**
     * @test création d'un bucket déjà créé -> ne fait rien
     */
    public function testCreateBucketAlreadyCreated(): void
    {
        $this->s3->createBucket('test-bucket');
        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));

        $this->s3->createBucket('test-bucket');
        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));
    }

    /**
     * @test création d'un bucket déjà créé par défault -> ne fait rien
     */
    public function testCreateBucketAlreadyCreatedDefault(): void
    {
        $this->s3->createBucket();
        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));

        $this->s3->createBucket();
        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));
    }

    /**
     * @test suppression d'un bucket d'un mauvais nom -> S3Exception
     */
    public function testDeleteBucketWrongName(): void
    {
        $this->expectException(S3Exception::class);

        $this->s3->deleteBucket('wr°ng-buck€t');
    }

    /**
     * @test suppression d'un bucket n'existant pas -> on ne fait rien
     */
    public function testDeleteBucketNoBucket(): void
    {
        $this->s3->deleteBucket('test-bucket');

        $this->assertFalse($this->s3->doesBucketExist('test-bucket'));
    }

    /**
     * @test suppression d'un bucket n'existant pas -> on ne fait rien
     */
    public function testDeleteBucketNoBucketDefault(): void
    {
        $this->s3->deleteBucket();

        $this->assertFalse($this->s3->doesBucketExist());
    }

    /**
     * @test suppression d'un bucket vide -> OK
     */
    public function testDeleteBucketPresentEmpty(): void
    {
        $this->s3->createBucket('test-bucket');
        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));

        $this->s3->deleteBucket('test-bucket');
        $this->assertFalse($this->s3->doesBucketExist('test-bucket'));
    }

    /**
     * @test ajout d'un object OK
     */
    public function testPutObjectOk(): void
    {
        $this->s3->createBucket('test-bucket');
        $this->s3->putObject('test-text', 'key.txt', 'text/plain', 'test-bucket');

        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));
        $this->assertTrue($this->s3->doesObjectExist('key.txt', 'test-bucket'));
        $this->assertSame('test-text', $this->s3->getObjectContent('key.txt', 'test-bucket'));
        $this->assertSame('test-text', $this->s3->getObject('key.txt', 'test-bucket')->get('Body')->getContents());
    }

    /**
     * @test ajout d'un object du bucket par défault OK
     */
    public function testPutObjectDefaultOk(): void
    {
        $this->s3->createBucket();
        $this->s3->putObject('test-text', 'key.txt', 'text/plain');

        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));
        $this->assertTrue($this->s3->doesObjectExist('key.txt'));
        $this->assertSame('test-text', $this->s3->getObjectContent('key.txt'));
        $this->assertSame('test-text', $this->s3->getObject('key.txt')->get('Body')->getContents());
    }

    /**
     * @test ajout d'un object sur un bucket n'existant pas
     */
    public function testPutObjectNonBucket(): void
    {
        $this->expectException(S3Exception::class);

        $this->s3->putObject('test', 'keyName.txt', 'text/plain', 'test-bucket');
    }

    /**
     * @test delete d'un object OK
     */
    public function testDeleteObjectOk(): void
    {
        $this->s3->createBucket('test-bucket');
        $this->s3->putObject('test-text', 'key.txt', 'text/plain', 'test-bucket');

        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));
        $this->assertTrue($this->s3->doesObjectExist('key.txt', 'test-bucket'));

        $this->assertTrue($this->s3->deleteObject('key.txt', 'test-bucket'));
        $this->assertFalse($this->s3->doesObjectExist('key.txt', 'test-bucket'));
    }


    /**
     * @test delete d'un object d'un bucket par défaut OK
     */
    public function testDeleteObjectDefaultOk(): void
    {
        $this->s3->createBucket();
        $this->s3->putObject('test-text', 'key.txt', 'text/plain');

        $this->assertTrue($this->s3->doesBucketExist('test-bucket'));
        $this->assertTrue($this->s3->doesObjectExist('key.txt'));

        $this->assertTrue($this->s3->deleteObject('key.txt'));
        $this->assertFalse($this->s3->doesObjectExist('key.txt'));
    }

    /**
     * @test delete d'un object n'existant pas -> retourne false
     */
    public function testDeleteObjectDoesNotExist(): void
    {
        $this->assertFalse($this->s3->deleteObject('key.txt', 'test-bucket'));

        $this->s3->createBucket('test-bucket');

        $this->assertFalse($this->s3->deleteObject('key2.txt', 'test-bucket'));
    }

    /**
     * @test delete d'un object n'existant pas -> retourne false
     */
    public function testDeleteObjectDoesNotExistDefault(): void
    {
        $this->assertFalse($this->s3->deleteObject('key.txt'));

        $this->s3->createBucket();

        $this->assertFalse($this->s3->deleteObject('key2.txt'));
    }

    /**
     * @test get d'un object où le bucket n'existe pas
     */
    public function testGetObjectNoBucket(): void
    {
        $this->expectException(S3Exception::class);

        $this->s3->getObject('key', 'test-bucket');
    }

    /**
     * @test get d'un object où le bucket n'existe pas
     */
    public function testGetObjectNoBucketDefault(): void
    {
        $this->expectException(S3Exception::class);

        $this->s3->getObject('key');
    }

    /**
     * @test get d'un object où l'object n'existe pas
     */
    public function testGetObjectNoKey(): void
    {
        $this->s3->createBucket('test-bucket');

        $this->expectException(S3Exception::class);

        $this->s3->getObject('key', 'test-bucket');
    }

    /**
     * @test get d'un object où l'object n'existe pas
     */
    public function testGetObjectNoKeyDefault(): void
    {
        $this->s3->createBucket();

        $this->expectException(S3Exception::class);

        $this->s3->getObject('key');
    }

    /**
     * @test putFile d'un fichier non existant -> exception
     */
    public function testPutFileDoesNotExist(): void
    {
        $this->s3->createBucket();

        $this->expectException(RuntimeException::class);

        $this->s3->putFile('file.txt', 'key.txt', 'text/plain');
    }

    /**
     * @test putFile d'un fichier présent -> OK
     */
    public function testPutFileOk(): void
    {
        $this->s3->createBucket();

        $this->s3->putFile('/app/tests/fixtures/test.txt', 'test.txt', 'text/plain');

        $this->assertTrue($this->s3->doesObjectExist('test.txt'));
        $this->assertSame('ceci est le contenu du fichier.', $this->s3->getObjectContent('test.txt'));
    }

    /**
     * @test ::getObjects()
     */
    public function testGetObjects(): void
    {
        $this->assertSame([], $this->s3->getObjects());

        $this->s3->createBucket('test-bucket');
        $this->assertSame(['test-bucket' => []], $this->s3->getObjects());

        $this->s3->putObject('text', 'keyname.txt', 'text/plain', 'test-bucket');
        $this->assertSame(['test-bucket' => ['keyname.txt']], $this->s3->getObjects());

        $this->s3->putObject('text', 'keyname2.txt', 'text/plain', 'test-bucket');
        $this->assertSame(['test-bucket' => ['keyname.txt', 'keyname2.txt']], $this->s3->getObjects());
    }

    /**
     * @test ::getObjects() avec le bucketName par défaut
     */
    public function testGetObjectsDefault(): void
    {
        $this->assertSame([], $this->s3->setBucketName('test-bucket')->getObjects());

        $this->s3->createBucket();
        $this->assertSame(['test-bucket' => []], $this->s3->getObjects());

        $this->s3->putObject('text', 'keyname.txt', 'text/plain');
        $this->assertSame(['test-bucket' => ['keyname.txt']], $this->s3->getObjects());

        $this->s3->putObject('text', 'keyname2.txt', 'text/plain');
        $this->assertSame(['test-bucket' => ['keyname.txt', 'keyname2.txt']], $this->s3->getObjects());
    }

    /**
     * @test ::putDirectory() d'un dossier non existant
     */
    public function testPutDirectoryNotDirectory(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('/not/a/directory is not readable');

        $this->s3->putDirectory('/not/a/directory');
    }

    /**
     * @test ::putDirectory d'un dossier avec un fichier
     */
    public function testPutDirectoryOneFile(): void
    {
        $this->s3->createBucket();

        $this->s3->putDirectory('/app/tests/fixtures/directory');
        $this->assertTrue($this->s3->doesObjectExist('test.txt'));
        $this->s3->addObjectKey('test-bucket', 'test.txt');

        $this->s3->putDirectory('/app/tests/fixtures/directory', 'directory');
        $this->assertTrue($this->s3->doesObjectExist('directory/test.txt'));
        $this->s3->addObjectKey('test-bucket', 'directory/test.txt');
    }

    /**
     * @test ::putDirectory de plusieurs dossiers
     */
    public function testPutDirectoryMultipleDirectoriesPrefix(): void
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

    /**
     * @test ::putDirectory de plusieurs dossiers sans préfix
     */
    public function testPutDirectoryMultipleDirectoriesNoPrefix(): void
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
