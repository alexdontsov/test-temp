<?php

declare(strict_types=1);

namespace App\S3Bucket\Application\Services;

use App\S3Bucket\Domain\Services\S3ExportServiceInterface;
use App\S3Bucket\Domain\Services\S3ZipServiceInterface;
use App\S3Bucket\Domain\Exceptions\DirNotFoundException;
use App\S3Bucket\Application\Exceptions\ObjectsNotFoundException;
use App\S3Bucket\Domain\Exceptions\TooLargeException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3ExportService implements S3ExportServiceInterface
{
    public const MAX_OBJECT_SIZE = 1073741824; //  1024 * 1024 * 1024 bytes = 1GB

    public function __construct(
        private readonly S3ZipServiceInterface $zipService
    ) {
    }

    /**
     * @inheritDoc
     * @throws ObjectsNotFoundException
     */
    public function exportObjects(string $bucketName, array $objectList): string
    {
        $bucket = $this->getBucket($bucketName);

        $this->validationPath($bucket, $objectList);
        $this->validationSize($bucket, $objectList);

        if (count($objectList) === 1 && $bucket->fileExists($objectList[0])) {
            return $this->exportOneFile($bucket, $objectList[0]);
        }

        return $this->zipService->archive($bucket, $objectList);
    }

    /**
     * Валидация общего размера выгружаемых объектов
     *
     * @param Filesystem $bucket
     * @param array $objectList
     * @return void
     * @throws TooLargeException
     */
    private function validationSize(Filesystem $bucket, array $objectList): void
    {
        $size = 0;

        foreach ($objectList as $objectPath) {
            // Для файла сразу посчитаем и прекратим итерацию
            if ($bucket->fileExists($objectPath)) {
                $size += $bucket->size($objectPath);
            }

            $allFiles = $bucket->allFiles($objectPath);

            $size += $this->calcPathSize($bucket, $allFiles);
        }

        if ($size > self::MAX_OBJECT_SIZE) {
            throw new TooLargeException();
        }
    }

    /**
     * Проверка переданных путей на их наличие
     *
     * @param Filesystem $bucket
     * @param array $objectList
     * @return void
     * @throws ObjectsNotFoundException
     */
    private function validationPath(Filesystem $bucket, array $objectList): void
    {
        $errors = [];

        /**
         * Это итератор по массиву присланных путей.
         * Чтобы в ошибке валидации указать какой по номеру адрес не найден.
         * Начинается с -1 чтобы можно было сразу увеличить до нуля и внутри цикли использовать ранний выход
         */
        $i = -1;

        foreach ($objectList as $pathItm) {
            $pathInfo = pathinfo($pathItm);
            $i++;

            // Если есть расширение, считаем, что это файл
            if (isset($pathInfo['extension'])) {
                if (!$bucket->fileExists($pathItm)) {
                    $errors[$i] = trans('settings.config.file-not-found', ['fileName' => $pathItm]);
                }

                continue;
            }

            // Для путей без расширения, считаем, что это папка
            if (!$bucket->directoryExists($pathItm)) {
                $errors[$i] = trans('settings.config.dir-not-found', ['dirName' => $pathItm]);
            }
        }

        if (count($errors) > 0) {
            throw new ObjectsNotFoundException($errors);
        }
    }

    /**
     * @inheritDoc
     */
    public function importFileToPath(string $bucketName, string $path, UploadedFile $file): void
    {
        $disk = $this->getBucket($bucketName);

        if ($path != '/' && !$disk->directoryExists($path)) {
            throw new DirNotFoundException($path);
        }

        $fileName = $file->getClientOriginalName();

        // todo тут раньше была проверка наличия файла, но теперь её нет, чтобы вернуть - ищи комит SDLLOG-1902

        $disk->putFileAs($path, $file, $fileName);
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

    /**
     * Экспорт одного файла без архивирования
     *
     * @param Filesystem $bucket
     * @param string $filePath
     *
     * @return string
     */
    private function exportOneFile(Filesystem $bucket, string $filePath): string
    {
        $content = $bucket->get($filePath);

        $pathInfo = pathinfo($filePath);
        $exportFileName = S3ZipService::EXPORT_ROOT_PATH . $pathInfo['basename'];

        Storage::disk('public')->put($exportFileName, $content);

        return Storage::disk('public')->path($exportFileName);
    }

    /**
     * Получить бакет
     *
     * @param string $bucketName
     * @return Filesystem
     */
    private function getBucket(string $bucketName): Filesystem
    {
        return Storage::disk("s3-config.$bucketName");
    }
}

