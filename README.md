# Amazon's SDK typed and documented wrapper class handling S3 object storages

[![Packagist Version](https://img.shields.io/packagist/v/rayanlevert/s3)](https://packagist.org/packages/rayanlevert/s3)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/rayanlevert/s3)](https://packagist.org/packages/rayanlevert/s3)

> Version **3.0** supports only **php8.4** with all brand new features, for >= **php8.1**, version **2.0** is still supported.

## How to use the class

### Construct
```php
// Initializes the client with credentials, endpoint to the API and server location
$oS3 = new \RayanLevert\S3\S3('accessKey', 'secretKey', 'endpoint', 'region');

/**
 * A 5th argument can be passed if you are going to use the same bucket from the instance 
 * you don't need to pass the bucket name to each method ;)
*/
$oS3 = new \RayanLevert\S3\S3('accessKey', 'secretKey', 'endpoint', 'region', 'bucketName');

// If you prefer to have all infos in an associative array, ::fromArray() is available
$oS3 = \RayanLevert\S3\S3::fromArray([
    'key'      => 'accessKey',
    'secret'   => 'secretKey',
    'endpoint' => 'endpoint-url',
    'region'   => 'region-dev',
    'bucket'   => 'bucketName' // optional if multiple buckets will be used
]);
```

## API methods
> Argument `$bucketName` is not mandatory if `$bucketName` has been passed to the constructor

```php
// If a bucket exists
public function doesBucketExist(string $bucketName = ''): bool;

// If an object exists by its bucket and key name
public function doesObjectExist(string $keyName, string $bucketName = ''): bool;

// Creates a bucket
public function createBucket(string $bucketName = ''): void

// Creates an object from a string
public function putObject(string $content, string $keyName, string $contentType, string $bucketName = ''): void

// Creates a object from a local file
public function putFile(string $filePath, string $keyName, string $contentType, string $bucketName = ''): void

// Returns an `\Aws\Result` instance from a key (throws an exception if not found)
public function getObject(string $key, string $bucketName = ''): \Aws\Result

// Returns the content of an object (throws an exception if not found)
public function getObjectContent(string $key, string $bucketName = ''): string

// Deletes a bucket (throws an exception if still objects remain in the bucket)
public function deleteBucket(string $bucketName = ''): bool

// Deletes an object (continues and returns false if the object didn't exist, true if it did)
public function deleteObject(string $keyName, string $bucketName = ''): bool

// @return array<string, string[]> Returns buckets and/or objects created from the instance (bucketName -> array of key names)
public function getObjects(): array;
```

## Development / Docker

> Uses [RustFS](https://github.com/rustfs/rustfs), open source object storage for local development (unit tests)

1. Copy [.env.example](.env.example) to `.env`

2. Start containers (`docker compose up -d`), for PHP and one for MinIO

3. Start `docker compose exec s3 bash` to access to the PHP

4. Run `composer install` to retrieve **vendors**

5. Go to http://localhost:9000 and connect by using username and password (`rustfsadmin` by default)

6. Go to  `Access Keys`, generate access and secret key and put them in `.env` file

7. Restart containers updating  `.env` file and you are good to go !