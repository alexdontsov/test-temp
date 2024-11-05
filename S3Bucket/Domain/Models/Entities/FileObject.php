<?php

declare(strict_types=1);

namespace App\S3Bucket\Domain\Models\Entities;

use App\S3Bucket\Domain\Models\ObjectInterface;
use DateTime;
use Illuminate\Contracts\Support\Arrayable;

class FileObject implements Arrayable, ObjectInterface
{
    private string $path;
    private DateTime $updatedAt;
    private int $size;
    private ?string $body;

    public function __construct(string $path, DateTime $updatedAt, int $size, ?string $body = null)
    {
        $this->path = $path;
        $this->updatedAt = $updatedAt;
        $this->size = $size;
        $this->body = $body;
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
        return ObjectInterface::FILE;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Дата-время последней модификации
     *
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Содержимое файла
     *
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'updated_at' => $this->updatedAt,
            'size' => $this->size,
            'body' => $this->body,
        ];
    }
}
