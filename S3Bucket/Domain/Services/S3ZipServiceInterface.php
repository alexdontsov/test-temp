<?php

declare(strict_types=1);

namespace App\S3Bucket\Domain\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use League\Flysystem\FilesystemException;

interface S3ZipServiceInterface
{
    /**
     * Архивировать файлы/папки по пути
     *
     * @param Filesystem $bucket
     * @param array $objectList
     * @return string
     *
     * @throws FilesystemException
     */
    public function archive(Filesystem $bucket, array $objectList): string;
}
