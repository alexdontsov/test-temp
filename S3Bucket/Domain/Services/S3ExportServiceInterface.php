<?php

declare(strict_types=1);


namespace App\S3Bucket\Domain\Services;

use App\S3Bucket\Domain\Exceptions\DirNotFoundException;
use App\S3Bucket\Application\Exceptions\FileAlreadyExistsException;
use App\S3Bucket\Domain\Exceptions\FileNotFoundException;
use App\S3Bucket\Domain\Exceptions\TooLargeException;
use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface S3ExportServiceInterface
{
    /**
     * Экспортировать список объектов
     * Возвращает адрес архива в локальной файловой системе.
     *
     * @param string $bucketName
     * @param array $objectList - список объектов
     *
     * @return string
     *
     * @throws FilesystemException
     * @throws TooLargeException
     * @throws FileNotFoundException
     * @throws DirNotFoundException
     */
    public function exportObjects(string $bucketName, array $objectList): string;

    /**
     * Импорт файла по адресу
     *
     * @param string $bucketName
     * @param string $path
     * @param UploadedFile $file
     *
     * @return void
     *
     * @throws FileAlreadyExistsException
     * @throws DirNotFoundException
     */
    public function importFileToPath(string $bucketName, string $path, UploadedFile $file): void;
}
