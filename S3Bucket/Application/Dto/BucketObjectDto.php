<?php

declare(strict_types=1);

namespace App\S3Bucket\Application\Dto;

use App\S3Bucket\Application\Enums\BucketObjectEnum;

class BucketObjectDto
{
    public function __construct(
        public readonly string $bucketName,
        public readonly BucketObjectEnum $type,
        public readonly string $path,
        public readonly ?string $content = ''
    ) {
    }
}
