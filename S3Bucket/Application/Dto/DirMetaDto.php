<?php

declare(strict_types=1);

namespace App\S3Bucket\Application\Dto;

class DirMetaDto
{
    public function __construct(public readonly int $count, public readonly int $size)
    {
    }
}
