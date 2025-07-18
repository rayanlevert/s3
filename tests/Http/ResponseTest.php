<?php

namespace RayanLevert\S3\Tests\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
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
}
