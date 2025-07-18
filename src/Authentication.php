<?php

namespace RayanLevert\S3;

use SensitiveParameter;

/** Authentication interface for S3 (AWS Signature Version 4) */
readonly class Authentication
{
    /** Algorithm to sign the request with */
    public const string ALGORITHM = 'AWS4-HMAC-SHA256';

    /** Date header to sign the request with */
    public const string DATE_HEADER = 'X-Amz-Date';

    /**
     * @param string $key Access Key
     * @param string $secret Secret Access Key
     * @param string $region AWS server region
     */
    public function __construct(
        #[SensitiveParameter] protected string $key,
        #[SensitiveParameter] protected string $secret,
        protected string $region
    ) {}

    public function __debugInfo(): array
    {
        return [
            'key'    => '********',
            'secret' => '********',
            'region' => $this->region
        ];
    }
}