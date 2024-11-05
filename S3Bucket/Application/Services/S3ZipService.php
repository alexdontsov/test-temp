<?php

declare(strict_types=1);

namespace App\S3Bucket\Application\Services;

use App\S3Bucket\Domain\Services\S3ZipServiceInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use League\Flysystem\Filesystem as LeagueFilesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\ZipArchive\FilesystemZipArchiveProvider;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

class S3ZipService implements S3ZipServiceInterface
{
    public const EXPORT_ROOT_PATH = 'storage/export/';

    /**
     * @inheritdoc
     */
    public function archive(Filesystem $bucket, array $objectList): string
    {
        $zipPath = $this->getZipPathName($objectList);
        $adapter = $this->zipAdapter($zipPath);

        $pathInfo = pathinfo($objectList[0]);
        $rootPath = $pathInfo['dirname'];

        foreach ($objectList as $path) {
            // Если файл, то просто положим его в архив
            if ($bucket->fileExists($path)) {
                $this->zipFile($bucket, $path, $rootPath, $adapter);
                continue;
            }

            // Для папки сначала воссасдодим всю внутреннюю структуру  папок
            $this->createPathStructure($bucket, $path, $rootPath, $adapter);
            // потом пройдёмся по всем вложенным файлом
            foreach ($bucket->allFiles($path) as $filePath) {
                $this->zipFile($bucket, $filePath, $rootPath, $adapter);
            }
        }

        return public_path($zipPath) . '.zip';
    }

    /**
     * Архивировать 1 файл
     *
     * @param Filesystem $bucket
     * @param string $path
     * @param string $rootPath
     * @param LeagueFilesystem $zipper
     * @return void
     * @throws FilesystemException
     */
    private function zipFile(Filesystem $bucket, string $path, string $rootPath, LeagueFilesystem $zipper): void
    {
        $writePath = $this->getLocalPath($path, $rootPath);
        $content = $bucket->get($path);

        $zipper->write($writePath, $content);
    }

    /**
     * Получить локальный адрес объекта относительно корневного адреса
     * Если экспортируется папка /path/inside
     * То для файла по полному адресу  /path/inside/path2/file.name
     * Локальный адрес должен быть /path2/file.name
     *
     * @param string $fullPath
     * @param string $rootPath
     * @return string
     */
    private function getLocalPath(string $fullPath, string $rootPath): string
    {
        $pathTmp = trim($fullPath, "/");
        $rootDir = trim($rootPath, "/");
        if ($rootDir === '.') {
            $rootDir = "";
        }

        $pos = strpos($pathTmp, $rootDir);
        return $pos !== false ? substr_replace($pathTmp, '', $pos, strlen($rootDir)) : $pathTmp;
    }

    /**
     * Выстроить иерархию папок как в оригинальной структуре
     *
     * @param Filesystem $bucket
     * @param string $path
     * @param string $rootPath
     * @param LeagueFilesystem $zipper
     * @return void
     * @throws FilesystemException
     */
    private function createPathStructure(Filesystem $bucket, string $path, string $rootPath, LeagueFilesystem $zipper): void
    {
        $writeDir = $this->getLocalPath($path, $rootPath);
        $zipper->createDirectory($writeDir);

        foreach ($bucket->directories($path, true) as $dirPath) {
            $writeDir = $this->getLocalPath($dirPath, $rootPath);
            $zipper->createDirectory($writeDir);
        }
    }

    /**
     * Имя для архива:
     * Если в списке 1 элемент то название строится: "название объекта".zip
     * Если 2 и более элементов, то название строится: "export_s3_yyyy-mm-dd_hh:mm:ss".zip
     *
     * @param array $objectList
     * @return string
     */
    private function getZipPathName(array $objectList): string
    {
        if (count($objectList) == 1) {
            $pathInfo = pathinfo($objectList[0]);

            return self::EXPORT_ROOT_PATH . $pathInfo['basename'];
        }

        return self::EXPORT_ROOT_PATH . 'export_s3_' . date('Y-m-d_H-i-s');
    }

    /**
     * Получить адаптер для запаковки файлов
     *
     * @param string $zipName
     * @return LeagueFilesystem
     */
    private function zipAdapter(string $zipName): LeagueFilesystem
    {
        $zipName .= '.zip';
        $provider = new FilesystemZipArchiveProvider(public_path($zipName));
        $adapter = new ZipArchiveAdapter($provider);

        return new LeagueFilesystem($adapter);
    }
}

