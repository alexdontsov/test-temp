<?php

declare(strict_types=1);

namespace App\S3Bucket\Application\Dto;

use DateTime;

class FileMetaDto
{
    public function __construct(public readonly DateTime $lastModified, public readonly int $size)
    {
    }
}
