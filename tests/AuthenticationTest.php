<?php

namespace RayanLevert\S3\Tests;

use RayanLevert\S3\Authentication;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(Authentication::class)]
class AuthenticationTest extends TestCase
{
    #[Test]
    public function debugInfo(): void
    {
        $oAuth = new Authentication('key', 'secret', 'region');

        $this->assertSame(['key' => '********', 'secret' => '********', 'region' => 'region'], $oAuth->__debugInfo());
    }
}