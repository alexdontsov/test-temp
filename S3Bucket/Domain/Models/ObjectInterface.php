<?php

declare(strict_types=1);

namespace App\S3Bucket\Domain\Models;

interface ObjectInterface
{
    public const FILE = 'file';
    public const FOLDER = 'folder';

    /**
     * Адрес объекта
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Тип объекта: файл или папка
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Размер объекта в байтах
     *
     * @return int
     */
    public function getSize(): int;
}
