<?php

declare(strict_types=1);

namespace App\S3Bucket\Presentation\Http\Resources;

use App\S3Bucket\Domain\Models\ObjectInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ObjectInterface $fileObject */
        $fileObject = $this->resource;

        return [
            'path' => $fileObject->getPath(),
            'type' => $fileObject->getType(),
            'updated_at' => $fileObject->getUpdatedAt()->format('Y-m-d H:i:s'),
            'size' => $fileObject->getSize(),
            'body' => $fileObject->getBody(),
        ];
    }
}
