<?php

declare(strict_types=1);

namespace App\S3Bucket\Domain\Models\Entities;

use App\S3Bucket\Domain\Models\ObjectInterface;
use Illuminate\Contracts\Support\Arrayable;

class FolderObject implements Arrayable, ObjectInterface
{
    private string $path;
    private int $objectsCount;
    private int $size;

    private array $childObjects = [];

    public function __construct(string $path, int $objectsCount, int $size)
    {
        $this->path = $path;
        $this->objectsCount = $objectsCount;
        $this->size = $size;
    }

    /**
     * Добавить дочерний объект
     *
     * @param ObjectInterface $object
     * @return void
     */
    public function pushChild(ObjectInterface $object): void
    {
        $this->childObjects[] = $object;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return ObjectInterface::FOLDER;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Количество вложенных объектов
     * Всего с учётом подпапок
     *
     * @return int
     */
    public function getObjectsCount(): int
    {
        return $this->objectsCount;
    }

    /**
     * @return array
     */
    public function getChildObjects(): array
    {
        return $this->childObjects;
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'type' => ObjectInterface::FOLDER,
            'objects_count' => $this->objectsCount,
            'size' => $this->size,
            'children' => $this->childObjects,
        ];
    }
}
