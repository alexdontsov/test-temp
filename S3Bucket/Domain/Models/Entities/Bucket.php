<?php

declare(strict_types=1);


namespace App\S3Bucket\Domain\Models\Entities;

use Illuminate\Contracts\Support\Arrayable;

class Bucket implements Arrayable
{
    private string $name;
    private int $size;
    private int $objectCount;

    public function __construct(string $name, int $size, int $objectCount)
    {
        $this->name = $name;
        $this->size = $size;
        $this->objectCount = $objectCount;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getObjectCount(): int
    {
        return $this->objectCount;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'size' => $this->size,
            'object_count' => $this->objectCount,
        ];
    }
}
