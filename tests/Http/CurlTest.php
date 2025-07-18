<?php

namespace RayanLevert\S3\Tests\Http;

use RayanLevert\S3\Http\Curl;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\S3\Http\Exception;

#[CoversClass(Curl::class)]
class CurlTest extends TestCase
{
    #[Test]
    public function constructorException(): void
    {
        $this->expectException(Exception::class);

        new Curl('not a valid url');
    }

    #[Test]
    public function constructor(): void
    {
        $curl = new Curl('http://localhost:9000');

        $this->assertInstanceOf(Curl::class, $curl);
        $this->assertSame('http://localhost:9000/', $curl->baseUri);
    }

    #[Test]
    public function uri(): void
    {
        $curl = new Curl('http://localhost:9000');

        $this->assertSame('http://localhost:9000/', $curl->uri);

        $curl->endpoint = 'test';

        $this->assertSame('http://localhost:9000/test', $curl->uri);
    }
}