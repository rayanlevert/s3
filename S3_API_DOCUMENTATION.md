# S3 API Documentation for cURL Implementation

This document provides comprehensive guidance for implementing S3 API endpoints using cURL without relying on Amazon's SDK. It covers authentication, request signing, and all major S3 operations.

## Table of Contents

1. [Authentication & Request Signing](#authentication--request-signing)
2. [Bucket Operations](#bucket-operations)
3. [Object Operations](#object-operations)
4. [Multipart Upload](#multipart-upload)
5. [Error Handling](#error-handling)
6. [PHP Implementation Examples](#php-implementation-examples)

## Authentication & Request Signing

### AWS Signature Version 4 (SigV4)

S3 uses AWS Signature Version 4 for authentication. You need to sign your requests with your AWS access key and secret key.

#### Required Headers for Authentication:
- `Authorization`: Contains the signature
- `X-Amz-Date`: ISO 8601 timestamp
- `X-Amz-Content-Sha256`: SHA256 hash of the request body (empty string for GET requests)
- `Host`: The S3 endpoint hostname

#### Signature Components:
1. **Canonical Request**: HTTP method, URI, query string, headers, and body hash
2. **String to Sign**: Algorithm, timestamp, credential scope, and canonical request hash
3. **Signature**: HMAC-SHA256 of the string to sign using your secret key

## Bucket Operations

### 1. List Buckets (GET /)

**Endpoint**: `GET /`

**Headers**:
```
Host: s3.amazonaws.com
X-Amz-Date: 20231201T120000Z
Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=host;x-amz-date,Signature=...
```

**cURL Example**:
```bash
curl -X GET "https://s3.amazonaws.com/" \
  -H "Host: s3.amazonaws.com" \
  -H "X-Amz-Date: 20231201T120000Z" \
  -H "Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=host;x-amz-date,Signature=..."
```

### 2. Create Bucket (PUT /{bucket})

**Endpoint**: `PUT /{bucket}`

**Headers**:
```
Host: {bucket}.s3.amazonaws.com
X-Amz-Date: 20231201T120000Z
Content-Length: 0
Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=host;x-amz-date,Signature=...
```

**cURL Example**:
```bash
curl -X PUT "https://my-bucket.s3.amazonaws.com/" \
  -H "Host: my-bucket.s3.amazonaws.com" \
  -H "X-Amz-Date: 20231201T120000Z" \
  -H "Content-Length: 0" \
  -H "Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=host;x-amz-date,Signature=..."
```

### 3. Delete Bucket (DELETE /{bucket})

**Endpoint**: `DELETE /{bucket}`

**Headers**:
```
Host: {bucket}.s3.amazonaws.com
X-Amz-Date: 20231201T120000Z
Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=host;x-amz-date,Signature=...
```

**cURL Example**:
```bash
curl -X DELETE "https://my-bucket.s3.amazonaws.com/" \
  -H "Host: my-bucket.s3.amazonaws.com" \
  -H "X-Amz-Date: 20231201T120000Z" \
  -H "Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=host;x-amz-date,Signature=..."
```

### 4. List Objects (GET /{bucket}?list-type=2)

**Endpoint**: `GET /{bucket}?list-type=2`

**Query Parameters**:
- `list-type=2`: Use version 2 of the list API
- `prefix`: Filter objects by prefix
- `delimiter`: Character used to group keys
- `max-keys`: Maximum number of keys to return
- `continuation-token`: Token for pagination

**Headers**:
```
Host: {bucket}.s3.amazonaws.com
X-Amz-Date: 20231201T120000Z
Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=host;x-amz-date,Signature=...
```

**cURL Example**:
```bash
curl -X GET "https://my-bucket.s3.amazonaws.com/?list-type=2&prefix=images/&max-keys=100" \
  -H "Host: my-bucket.s3.amazonaws.com" \
  -H "X-Amz-Date: 20231201T120000Z" \
  -H "Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=host;x-amz-date,Signature=..."
```

## Object Operations

### 1. Put Object (PUT /{bucket}/{key})

**Endpoint**: `PUT /{bucket}/{key}`

**Headers**:
```
Host: {bucket}.s3.amazonaws.com
X-Amz-Date: 20231201T120000Z
Content-Type: application/octet-stream
Content-Length: {file_size}
X-Amz-Content-Sha256: {body_hash}
Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=content-length;content-type;host;x-amz-content-sha256;x-amz-date,Signature=...
```

**cURL Example**:
```bash
curl -X PUT "https://my-bucket.s3.amazonaws.com/my-file.txt" \
  -H "Host: my-bucket.s3.amazonaws.com" \
  -H "X-Amz-Date: 20231201T120000Z" \
  -H "Content-Type: text/plain" \
  -H "Content-Length: 14" \
  -H "X-Amz-Content-Sha256: 2cf24dba5fb0a30e26e83b2ac5b9e29e1b161e5c1fa7425e73043362938b9824" \
  -H "Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=content-length;content-type;host;x-amz-content-sha256;x-amz-date,Signature=..." \
  --data-binary "Hello, World!"
```

### 2. Get Object (GET /{bucket}/{key})

**Endpoint**: `GET /{bucket}/{key}`

**Headers**:
```
Host: {bucket}.s3.amazonaws.com
X-Amz-Date: 20231201T120000Z
X-Amz-Content-Sha256: e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855
Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=host;x-amz-content-sha256;x-amz-date,Signature=...
```

**cURL Example**:
```bash
curl -X GET "https://my-bucket.s3.amazonaws.com/my-file.txt" \
  -H "Host: my-bucket.s3.amazonaws.com" \
  -H "X-Amz-Date: 20231201T120000Z" \
  -H "X-Amz-Content-Sha256: e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855" \
  -H "Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=host;x-amz-content-sha256;x-amz-date,Signature=..."
```

### 3. Delete Object (DELETE /{bucket}/{key})

**Endpoint**: `DELETE /{bucket}/{key}`

**Headers**:
```
Host: {bucket}.s3.amazonaws.com
X-Amz-Date: 20231201T120000Z
X-Amz-Content-Sha256: e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855
Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=host;x-amz-content-sha256;x-amz-date,Signature=...
```

**cURL Example**:
```bash
curl -X DELETE "https://my-bucket.s3.amazonaws.com/my-file.txt" \
  -H "Host: my-bucket.s3.amazonaws.com" \
  -H "X-Amz-Date: 20231201T120000Z" \
  -H "X-Amz-Content-Sha256: e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855" \
  -H "Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=host;x-amz-content-sha256;x-amz-date,Signature=..."
```

### 4. Head Object (HEAD /{bucket}/{key})

**Endpoint**: `HEAD /{bucket}/{key}`

**Headers**:
```
Host: {bucket}.s3.amazonaws.com
X-Amz-Date: 20231201T120000Z
X-Amz-Content-Sha256: e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855
Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=host;x-amz-content-sha256;x-amz-date,Signature=...
```

**cURL Example**:
```bash
curl -I "https://my-bucket.s3.amazonaws.com/my-file.txt" \
  -H "Host: my-bucket.s3.amazonaws.com" \
  -H "X-Amz-Date: 20231201T120000Z" \
  -H "X-Amz-Content-Sha256: e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855" \
  -H "Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=host;x-amz-content-sha256;x-amz-date,Signature=..."
```

## Multipart Upload

For large files (>5MB), use multipart upload:

### 1. Initiate Multipart Upload (POST /{bucket}/{key}?uploads)

**Endpoint**: `POST /{bucket}/{key}?uploads`

**Headers**:
```
Host: {bucket}.s3.amazonaws.com
X-Amz-Date: 20231201T120000Z
Content-Type: application/octet-stream
X-Amz-Content-Sha256: e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855
Authorization: AWS4-HMAC-SHA256 Credential=YOUR_ACCESS_KEY/20231201/us-east-1/s3/aws4_request,SignedHeaders=content-type;host;x-amz-content-sha256;x-amz-date,Signature=...
```

### 2. Upload Part (PUT /{bucket}/{key}?partNumber={part}&uploadId={uploadId})

**Endpoint**: `PUT /{bucket}/{key}?partNumber={part}&uploadId={uploadId}`

### 3. Complete Multipart Upload (POST /{bucket}/{key}?uploadId={uploadId})

**Endpoint**: `POST /{bucket}/{key}?uploadId={uploadId}`

## Error Handling

### Common HTTP Status Codes:

- **200**: Success
- **204**: Success (no content)
- **400**: Bad Request
- **401**: Unauthorized
- **403**: Forbidden
- **404**: Not Found
- **409**: Conflict (bucket already exists)
- **500**: Internal Server Error

### Error Response Format:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<Error>
  <Code>NoSuchBucket</Code>
  <Message>The specified bucket does not exist</Message>
  <Resource>/my-bucket</Resource>
  <RequestId>44425877C21DAD5</RequestId>
</Error>
```

## PHP Implementation Examples

### Basic S3 Client Class

```php
<?php

class S3Client {
    private string $accessKey;
    private string $secretKey;
    private string $region;
    private string $endpoint;
    
    public function __construct(string $accessKey, string $secretKey, string $region, string $endpoint = 's3.amazonaws.com') {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->region = $region;
        $this->endpoint = $endpoint;
    }
    
    public function listBuckets(): array {
        $url = "https://{$this->endpoint}/";
        $headers = $this->getSignedHeaders('GET', '/', '', []);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => 'GET'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $xml = simplexml_load_string($response);
            $buckets = [];
            foreach ($xml->Buckets->Bucket as $bucket) {
                $buckets[] = (string)$bucket->Name;
            }
            return $buckets;
        }
        
        throw new Exception("Failed to list buckets: HTTP $httpCode");
    }
    
    public function putObject(string $bucket, string $key, string $content, string $contentType = 'application/octet-stream'): bool {
        $url = "https://{$bucket}.{$this->endpoint}/{$key}";
        $headers = $this->getSignedHeaders('PUT', "/{$key}", $content, [
            'Content-Type' => $contentType,
            'Content-Length' => strlen($content)
        ], $bucket);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $content
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
    
    public function getObject(string $bucket, string $key): string {
        $url = "https://{$bucket}.{$this->endpoint}/{$key}";
        $headers = $this->getSignedHeaders('GET', "/{$key}", '', [], $bucket);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => 'GET'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return $response;
        }
        
        throw new Exception("Failed to get object: HTTP $httpCode");
    }
    
    private function getSignedHeaders(string $method, string $uri, string $body, array $additionalHeaders = [], string $bucket = ''): array {
        $timestamp = gmdate('Ymd\THis\Z');
        $date = gmdate('Ymd');
        
        // Canonical request
        $canonicalHeaders = "host:{$bucket}.{$this->endpoint}\n";
        $signedHeaders = 'host';
        
        foreach ($additionalHeaders as $key => $value) {
            $canonicalHeaders .= strtolower($key) . ":$value\n";
            $signedHeaders .= ';' . strtolower($key);
        }
        
        $canonicalHeaders .= "x-amz-content-sha256:" . hash('sha256', $body) . "\n";
        $canonicalHeaders .= "x-amz-date:$timestamp\n";
        $signedHeaders .= ';x-amz-content-sha256;x-amz-date';
        
        $canonicalRequest = $method . "\n" . $uri . "\n\n" . $canonicalHeaders . "\n" . $signedHeaders . "\n" . hash('sha256', $body);
        
        // String to sign
        $algorithm = 'AWS4-HMAC-SHA256';
        $credentialScope = "$date/{$this->region}/s3/aws4_request";
        $stringToSign = "$algorithm\n$timestamp\n$credentialScope\n" . hash('sha256', $canonicalRequest);
        
        // Calculate signature
        $dateKey = hash_hmac('sha256', $date, 'AWS4' . $this->secretKey, true);
        $dateRegionKey = hash_hmac('sha256', $this->region, $dateKey, true);
        $dateRegionServiceKey = hash_hmac('sha256', 's3', $dateRegionKey, true);
        $signingKey = hash_hmac('sha256', 'aws4_request', $dateRegionServiceKey, true);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);
        
        // Build authorization header
        $authorization = "$algorithm Credential={$this->accessKey}/$credentialScope,SignedHeaders=$signedHeaders,Signature=$signature";
        
        // Build headers array
        $headers = [
            "Host: {$bucket}.{$this->endpoint}",
            "X-Amz-Date: $timestamp",
            "X-Amz-Content-Sha256: " . hash('sha256', $body),
            "Authorization: $authorization"
        ];
        
        foreach ($additionalHeaders as $key => $value) {
            $headers[] = "$key: $value";
        }
        
        return $headers;
    }
}

// Usage example
$s3 = new S3Client('YOUR_ACCESS_KEY', 'YOUR_SECRET_KEY', 'us-east-1');

// List buckets
$buckets = $s3->listBuckets();
print_r($buckets);

// Upload a file
$success = $s3->putObject('my-bucket', 'test.txt', 'Hello, World!', 'text/plain');
echo $success ? "Upload successful\n" : "Upload failed\n";

// Download a file
$content = $s3->getObject('my-bucket', 'test.txt');
echo $content . "\n";
```

### Advanced Features

#### 1. Multipart Upload Implementation

```php
public function initiateMultipartUpload(string $bucket, string $key, string $contentType = 'application/octet-stream'): string {
    $url = "https://{$bucket}.{$this->endpoint}/{$key}?uploads";
    $headers = $this->getSignedHeaders('POST', "/{$key}?uploads", '', [
        'Content-Type' => $contentType
    ], $bucket);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => 'POST'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $xml = simplexml_load_string($response);
        return (string)$xml->UploadId;
    }
    
    throw new Exception("Failed to initiate multipart upload: HTTP $httpCode");
}

public function uploadPart(string $bucket, string $key, string $uploadId, int $partNumber, string $content): string {
    $url = "https://{$bucket}.{$this->endpoint}/{$key}?partNumber={$partNumber}&uploadId={$uploadId}";
    $headers = $this->getSignedHeaders('PUT', "/{$key}?partNumber={$partNumber}&uploadId={$uploadId}", $content, [
        'Content-Length' => strlen($content)
    ], $bucket);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => $content
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        // Extract ETag from response headers
        return 'etag'; // You'll need to extract this from curl_getinfo
    }
    
    throw new Exception("Failed to upload part: HTTP $httpCode");
}
```

#### 2. Presigned URLs

```php
public function generatePresignedUrl(string $bucket, string $key, string $method = 'GET', int $expires = 3600): string {
    $timestamp = time() + $expires;
    $date = gmdate('Ymd', $timestamp);
    
    // Create credential scope
    $credentialScope = "$date/{$this->region}/s3/aws4_request";
    
    // Create signed headers
    $signedHeaders = 'host';
    
    // Create canonical request
    $canonicalRequest = $method . "\n" . "/{$key}\n" . "X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=" . urlencode($this->accessKey . '/' . $credentialScope) . "&X-Amz-Date=" . gmdate('Ymd\THis\Z', $timestamp) . "&X-Amz-Expires={$expires}&X-Amz-SignedHeaders=" . $signedHeaders . "\n" . "host:{$bucket}.{$this->endpoint}\n\n" . $signedHeaders . "\nUNSIGNED-PAYLOAD";
    
    // Create string to sign
    $stringToSign = "AWS4-HMAC-SHA256\n" . gmdate('Ymd\THis\Z', $timestamp) . "\n" . $credentialScope . "\n" . hash('sha256', $canonicalRequest);
    
    // Calculate signature
    $dateKey = hash_hmac('sha256', $date, 'AWS4' . $this->secretKey, true);
    $dateRegionKey = hash_hmac('sha256', $this->region, $dateKey, true);
    $dateRegionServiceKey = hash_hmac('sha256', 's3', $dateRegionKey, true);
    $signingKey = hash_hmac('sha256', 'aws4_request', $dateRegionServiceKey, true);
    $signature = hash_hmac('sha256', $stringToSign, $signingKey);
    
    // Build URL
    $url = "https://{$bucket}.{$this->endpoint}/{$key}";
    $url .= "?X-Amz-Algorithm=AWS4-HMAC-SHA256";
    $url .= "&X-Amz-Credential=" . urlencode($this->accessKey . '/' . $credentialScope);
    $url .= "&X-Amz-Date=" . gmdate('Ymd\THis\Z', $timestamp);
    $url .= "&X-Amz-Expires={$expires}";
    $url .= "&X-Amz-SignedHeaders=" . $signedHeaders;
    $url .= "&X-Amz-Signature=" . $signature;
    
    return $url;
}
```

## Best Practices

1. **Error Handling**: Always check HTTP status codes and handle errors appropriately
2. **Retry Logic**: Implement exponential backoff for transient failures
3. **Connection Pooling**: Reuse cURL handles when possible
4. **Content Validation**: Verify file integrity using checksums
5. **Security**: Never log or expose your secret key
6. **Rate Limiting**: Respect S3's rate limits and implement throttling
7. **Monitoring**: Log request/response times and error rates

## Testing

Use tools like:
- **AWS CLI**: For comparison testing
- **Postman**: For API testing
- **Unit Tests**: For automated testing
- **Integration Tests**: For end-to-end testing

## Additional Resources

- [AWS S3 API Reference](https://docs.aws.amazon.com/AmazonS3/latest/API/Welcome.html)
- [AWS Signature Version 4](https://docs.aws.amazon.com/general/latest/gr/signature-version-4.html)
- [S3 Error Responses](https://docs.aws.amazon.com/AmazonS3/latest/API/ErrorResponses.html) 