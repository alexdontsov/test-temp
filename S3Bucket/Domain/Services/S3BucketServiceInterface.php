<?php

declare(strict_types=1);


namespace App\S3Bucket\Domain\Services;

use App\S3Bucket\Domain\Models\Entities\Bucket;
use App\S3Bucket\Domain\Models\ObjectInterface;
use App\S3Bucket\Application\Enums\BucketObjectEnum;
use App\S3Bucket\Application\Dto\BucketObjectDto;
use Illuminate\Support\Collection;

interface S3BucketServiceInterface
{
    /**
     * Получение информации по всем подключенным бакетам
     *
     * @return Collection|Bucket[]
     */
    public function getAllBuckets(): Collection;

    /**
     * Получить объект по присланному адресу
     *
     * @param string $bucketName
     * @param string $path
     *
     * @return ObjectInterface
     */
    public function getObjectByPath(string $bucketName, BucketObjectEnum $type, string $path): ObjectInterface;

    /**
     * Создание объекта для бакета (Файл или Папка)
     *
     * @param BucketObjectDto $bucketObjectDto
     * @return bool
     */
    public function createObject(BucketObjectDto $bucketObjectDto): bool;

    /**
     * Переместить(переименовать) объект
     *
     * @param string $bucketName
     * @param string $fromPath
     * @param string $toPath
     * @return bool
     */
    public function moveObject(string $bucketName, string $fromPath, string $toPath): bool;

    /**
     * Удаление объекта
     *
     * @param BucketObjectDto $bucketObjectDto
     * @return bool
     */
    public function deleteObject(BucketObjectDto $bucketObjectDto): bool;

}
