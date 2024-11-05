<?php

declare(strict_types=1);

namespace App\S3Bucket\Application\Services;

use App\S3Bucket\Domain\Services\S3BucketServiceInterface;
use App\S3Bucket\Domain\Models\Entities\Bucket;
use App\S3Bucket\Domain\Models\ObjectInterface;
use App\S3Bucket\Domain\Models\Entities\FolderObject;
use App\S3Bucket\Domain\Models\Entities\FileObject;
use App\S3Bucket\Application\Enums\BucketObjectEnum;
use App\S3Bucket\Domain\Exceptions\DirNotFoundException;
use App\S3Bucket\Domain\Exceptions\FileNotFoundException;
use App\S3Bucket\Application\Dto\BucketObjectDto;
use App\S3Bucket\Application\Dto\DirMetaDto;
use App\S3Bucket\Application\Dto\FileMetaDto;
use App\Common\Application\Utils\AppSettings;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class S3BucketService implements S3BucketServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getAllBuckets(): Collection
    {
        $buckets = AppSettings::get(['s3-config', 'buckets']);

        return collect($buckets)->map(function (string $bucket) {
            $disk = $this->getBucket($bucket);
            $files = $disk->allFiles();
            $size = $this->calcPathSize($disk, $files);

            return new Bucket($bucket, $size, count($files));
        });
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     * @throws DirNotFoundException
     */
    public function getObjectByPath(string $bucketName, BucketObjectEnum $type ,string $path): ObjectInterface
    {
        $bucket = $this->getBucket($bucketName);

        return match ($type) {
            BucketObjectEnum::FILE => $this->getFileData($bucket, $path),
            BucketObjectEnum::FOLDER => $this->getFolderData($bucket, $path),
            BucketObjectEnum::ROOT => $this->getRootData($bucket, $path),
        };
    }

    /**
     * @inheritdoc
     */
    public function createObject(BucketObjectDto $bucketObjectDto): bool
    {
        return match ($bucketObjectDto->type) {
            BucketObjectEnum::FILE => $this->getBucket($bucketObjectDto->bucketName)->put($bucketObjectDto->path, $bucketObjectDto->content),
            BucketObjectEnum::FOLDER => $this->getBucket($bucketObjectDto->bucketName)->makeDirectory($bucketObjectDto->path)
        };
    }

    /**
     * @inheritdoc
     */
    public function deleteObject(BucketObjectDto $bucketObjectDto): bool
    {
        return match ($bucketObjectDto->type) {
            BucketObjectEnum::FILE => $this->getBucket($bucketObjectDto->bucketName)->delete($bucketObjectDto->path),
            BucketObjectEnum::FOLDER => $this->getBucket($bucketObjectDto->bucketName)->deleteDirectory($bucketObjectDto->path)
        };
    }

    /**
     * @inheritdoc
     */
    public function moveObject(string $bucketName, string $fromPath, string $toPath): bool
    {
        return $this->getBucket($bucketName)->move($fromPath, $toPath);
    }

    /**
     * Подсчёт размера директории.
     * Ничего умнее рекурсивного опроса пока не придумалось
     *
     * @param Filesystem $storage
     * @param array $files
     *
     * @return int
     */
    private function calcPathSize(Filesystem $storage, array $files): int
    {
        $size = 0;

        foreach ($files as $file) {
            $size += $storage->size($file);
        }

        return $size;
    }

    private function getFolderData(Filesystem $bucket, string $path): FolderObject
    {
        if ($path === '/') {
            Throw new DirNotFoundException($path);
        }

        if (!$bucket->directoryExists($path)) {
            Throw new DirNotFoundException($path);
        }

        return $this->getFolderStructure($bucket, $path);
    }

    private function getFileData(Filesystem $bucket, string $path): FileObject
    {
        if ($path === '/') {
            Throw new FileNotFoundException($path);
        }

        if (!$bucket->exists($path)) {
            Throw new FileNotFoundException($path);
        }

        return $this->getFileStructure($bucket, $path);
    }

    private function getRootData(Filesystem $bucket, string $path): FolderObject
    {
        if (!$bucket->allFiles($path) && !$bucket->allDirectories($path)) {
            return new FolderObject("/", 0, 0);
        }

        return $this->getFolderStructure($bucket, $path);
    }

    /**
     * Полные данные файла
     *
     * @param Filesystem $bucket
     * @param string $path
     * @return FileObject
     */
    private function getFileStructure(Filesystem $bucket, string $path): FileObject
    {
        $fileMeta = $this->getFileMeta($bucket, $path);
        $body = $bucket->get($path);

        return new FileObject($path, $fileMeta->lastModified, $fileMeta->size, $body);
    }

    /**
     * Данные папки
     *
     * @param Filesystem $bucket
     * @param string $path
     *
     * @return FolderObject
     */
    private function getFolderStructure(Filesystem $bucket, string $path): FolderObject
    {
        $metaDto = $this->getDirMeta($bucket, $path);
        $needDir = new FolderObject($path, $metaDto->count, $metaDto->size);

        // Сначала папки
        $folders = $bucket->directories($path);
        foreach ($folders as $dirItm) {
            $metaDto = $this->getDirMeta($bucket, $dirItm);
            $needDir->pushChild(new FolderObject($dirItm, $metaDto->count, $metaDto->size));
        }

        // потом файлы (прямые потомки)
        $files = $bucket->files($path);
        foreach ($files as $file) {
            $fileMeta = $this->getFileMeta($bucket, $file);
            $needDir->pushChild(new FileObject($file, $fileMeta->lastModified, $fileMeta->size));
        }

        return $needDir;
    }

    /**
     * Получить метаданные файла
     *
     * @param Filesystem $bucket
     * @param string $path
     * @return FileMetaDto
     */
    private function getFileMeta(Filesystem $bucket, string $path): FileMetaDto
    {
        $lastModified = $bucket->lastModified($path);
        $lastModified = Carbon::createFromTimestamp($lastModified);
        $size = $bucket->size($path);

        return new FileMetaDto($lastModified->toDateTime(), $size);
    }

    /**
     * Получить метаданные папки
     *
     * @param Filesystem $bucket
     * @param string $path
     * @return DirMetaDto
     */
    private function getDirMeta(Filesystem $bucket, string $path): DirMetaDto
    {
        $files = $bucket->allFiles($path);
        $dirs = count($bucket->allDirectories($path));

        $size = $this->calcPathSize($bucket, $files);

        return new DirMetaDto(count($files) + $dirs, $size);
    }

    /**
     * Получить бакет
     *
     * @param string $bucketName
     * @return Filesystem
     */
    private function getBucket(string $bucketName): Filesystem
    {
        return Storage::disk("s3-config.{$bucketName}");
    }
}

