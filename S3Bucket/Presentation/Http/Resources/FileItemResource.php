<?php

declare(strict_types=1);

namespace App\S3Bucket\Presentation\Http\Resources;

use App\S3Bucket\Domain\Models\Entities\FileObject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var FileObject $resource */
        $object = $this->resource;

        return [
            'path' => $object->getPath(),
            'type' => $object->getType(),
            'updated_at' => $object->getUpdatedAt()->format('Y-m-d H:i:s'),
            'size' => $object->getSize(),
        ];
    }
}
