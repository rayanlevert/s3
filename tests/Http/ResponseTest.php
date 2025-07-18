<?php

namespace RayanLevert\S3\Tests\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RayanLevert\S3\Http\Curl;
use RayanLevert\S3\Http\Exception;
use RayanLevert\S3\Http\Response;

#[CoversClass(Response::class)]
class ResponseTest extends TestCase
{
    #[Test]
    public function constructor(): void
    {
        $response = new Response('https://example.com', 'body', 200, ['header' => 'value']);

        $this->assertSame('https://example.com', $response->url);
        $this->assertSame('body', $response->body);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame(['header' => 'value'], $response->headers);
    }

    #[Test]
    public function xmlException(): void
    {
        $this->expectException(Exception::class);

        new Response('https://example.com', 'invalid xml', 200, ['header' => 'value'])->xml();
    }

    #[Test]
    public function xml(): void
    {
        $response = new Response(
            'https://example.com',
            '<?xml version="1.0" encoding="UTF-8"?><Response><Body>body</Body></Response>',
            200,
            ['header' => 'value']
        );

        $this->assertInstanceOf(\SimpleXMLElement::class, $oXml = $response->xml());
        $this->assertSame('body', (string) $oXml->Body);
    }

    #[Test]
    public function fromCurlException(): void
    {
        $curl = $this->getMockBuilder(Curl::class)
            ->onlyMethods(['exec'])
            ->setConstructorArgs(['http://localhost:9000'])
            ->getMock();

        $curl->expects($this->once())->method('exec')->willReturn(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to execute cURL Request');

        Response::fromCurl($curl);
    }

    #[Test]
    public function fromCurl(): void
    {
        $curl = $this->getMockBuilder(Curl::class)
            ->onlyMethods(['exec', 'getInfo'])
            ->setConstructorArgs(['http://localhost:9000'])
            ->getMock();

        $curl->expects($this->once())->method('exec')->willReturn('body');
        $curl->expects($this->once())->method('getInfo')->willReturn(['http_code' => 200, 'url' => 'http://localhost:9000']);

        $response = Response::fromCurl($curl);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('http://localhost:9000', $response->url);
        $this->assertSame('body', $response->body);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame(['http_code' => 200, 'url' => 'http://localhost:9000'], $response->headers);
    }
}
