<?php

namespace RayanLevert\S3\Tests\Http;

use RayanLevert\S3\Http\Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RayanLevert\S3\Http\Client;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\S3\Http\Curl;
use ReflectionProperty;

#[CoversClass(Client::class)]
class ClientTest extends TestCase
{
    #[Test]
    public function constructorException(): void
    {
        $this->expectException(Exception::class);

        new Client('not a valid url');
    }

    #[Test]
    public function constructor(): void
    {
        $client = new Client('http://localhost:9000');

        $this->assertInstanceOf(Client::class, $client);

        $oCurl = new ReflectionProperty($client, 'curl');

        $this->assertInstanceOf(Curl::class, $oCurl = $oCurl->getValue($client));
        $this->assertSame('http://localhost:9000/', $oCurl->baseUri);
    }
}